<?php

namespace App\Console\Commands;

use App\Events\CheckoutableCheckedIn;
use App\Mail\BulkDeleteReportMail;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Helper\ProgressBar;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multisearch;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;

class BulkDelete extends Command
{
    protected $signature = 'hsbit:checkin-delete-items';

    protected $description = 'Thu hồi hoặc xóa hạng mục theo công ty và loại bằng chế độ tương tác.';

    private const CHECKIN_NOTE = 'Thu hồi bằng thao tác CLI hàng loạt';

    private array $reportLines = [];

    public function handle(): int
    {
        // Step 1: Dry run?
        $dryRun = confirm(
            label: 'Đây có phải lần chạy thử không?',
            default: true,
            yes: 'Có - chỉ xem trước, không ghi thay đổi',
            no: 'Không - CHẠY THẬT, sẽ ghi thay đổi',
        );

        // Step 2: Who are you?
        $adminId = search(
            label: 'Who are you? Search by username, first or last name.',
            placeholder: 'Type to search users...',
            options: function (string $value): array {
                if (strlen($value) < 1) {
                    return [];
                }

                return User::where('activated', 1)
                    ->whereNull('deleted_at')
                    ->onlySuperAdmins()
                    ->where(function ($query) use ($value) {
                        $query->where('username', 'like', "%{$value}%")
                            ->orWhere('first_name', 'like', "%{$value}%")
                            ->orWhere('last_name', 'like', "%{$value}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$value}%"]);
                    })
                    ->get()
                    ->mapWithKeys(fn (User $u) => [$u->id => "{$u->first_name} {$u->last_name} ({$u->username})"])
                    ->toArray();
            },
            validate: fn (mixed $value) => ! $value ? 'Bắt buộc chọn một người dùng đang hoạt động hợp lệ.' : null,
        );

        /** @var User $admin */
        $admin = User::findOrFail((int) $adminId);

        // Step 3: Which companies?
        if (! Company::exists()) {
            error('Không tìm thấy công ty nào. Hãy tạo ít nhất một công ty trước khi dùng lệnh này.');

            return 1;
        }

        $selectedCompanyKeys = multisearch(
            label: 'Bạn muốn thu hồi và xóa hạng mục cho công ty nào?',
            placeholder: 'Nhập để tìm công ty...',
            options: function (string $value): array {
                $results = [];

                if ($value === '' || str_contains('(no company / unassigned)', strtolower($value))) {
                    $results['__null__'] = '(Không có công ty / Chưa gán)';
                }

                $query = Company::orderBy('name');
                if ($value !== '') {
                    $query->where('name', 'like', "%{$value}%");
                }

                $query->get()->each(function (Company $c) use (&$results) {
                    $results[$c->id] = "{$c->name} (ID: {$c->id})";
                });

                return $results;
            },
            scroll: 10,
            required: 'Vui lòng chọn ít nhất một công ty.',
            hint: 'If you\'re searching on several differently named companies, use the up-arrow to go back to the search box to search again. ',
        );

        $includeNullCompany = in_array('__null__', $selectedCompanyKeys);
        $selectedCompanyIds = array_values(array_filter(
            $selectedCompanyKeys,
            fn ($k) => $k !== '__null__'
        ));

        $companyNamesById = Company::whereIn('id', $selectedCompanyIds)->pluck('name', 'id')->toArray();
        $selectedCompanyNames = array_map(
            fn ($id) => $id === '__null__' ? '(Không có công ty)' : ($companyNamesById[$id] ?? "(ID: {$id})"),
            $selectedCompanyKeys
        );

        // Step 4: Which item types?
        $rawTypeSelection = multiselect(
            label: 'Bạn muốn thu hồi và xóa loại hạng mục nào?',
            options: [
                'all' => 'Tất cả hạng mục (tài sản, license, phụ kiện, linh kiện, vật tư, người dùng)',
                'assets' => 'Tài sản',
                'licenses' => 'License',
                'accessories' => 'Phụ kiện',
                'components' => 'Linh kiện',
                'consumables' => 'Vật tư',
                'users' => 'Người dùng',
            ],
            required: 'Vui lòng chọn ít nhất một loại hạng mục.',
            hint: 'Chọn "Tất cả hạng mục" để xử lý mọi loại được hỗ trợ.',
        );

        $allSubTypes = ['assets', 'licenses', 'accessories', 'components', 'consumables', 'users'];
        $selectedTypes = in_array('all', $rawTypeSelection)
            ? $allSubTypes
            : array_values(array_intersect($allSubTypes, $rawTypeSelection));

        // Compute and display counts now so the user can see what will be affected
        $counts = $this->getCounts($selectedTypes, $selectedCompanyIds, $includeNullCompany);

        $skipAdminUser = false;

        $this->line('');
        $this->line('  Items that would be affected:');
        foreach ($counts as $type => $count) {
            $this->line(sprintf('    %-14s %d', ucfirst($type).':', $count));
        }

        if (in_array('users', $selectedTypes)) {
            $userInScope = $this->buildUserQuery($selectedCompanyIds, $includeNullCompany)
                ->where('users.id', $admin->id)
                ->exists();

            if ($userInScope) {
                $skipAdminUser = true;
                $counts['users'] = max(0, ($counts['users'] ?? 0) - 1);
                warning("  Your user ({$admin->username}) is within the selected scope and will be skipped during user deletion.");
            }
        }

        $this->line('');

        // Step 5: Hard delete, soft delete, or no delete?
        $deleteType = select(
            label: 'Xóa hạng mục theo cách nào?',
            options: [
                'soft' => 'Xóa mềm - chuyển vào thùng rác (có thể khôi phục)',
                'hard' => 'Xóa vĩnh viễn - không thể khôi phục',
                'none' => 'Không xóa - chỉ thu hồi, hạng mục vẫn nằm trong kho',
            ],
            default: 'soft',
        );

        // Step 6: Send checkin notifications? (not applicable to users or consumables)
        $notifiableTypes = array_intersect($selectedTypes, ['assets', 'licenses', 'accessories', 'components']);
        $sendNotifications = false;

        if (! empty($notifiableTypes)) {
            $sendNotifications = confirm(
                label: 'Có gửi thông báo thu hồi không?',
                default: true,
                hint: 'Áp dụng cho: '.implode(', ', $notifiableTypes).'. Người dùng and consumables are excluded.',
            );
        }

        // Step 7: Clear related action_logs?
        $clearLogs = confirm(
            label: 'Có xóa nhật ký thao tác liên quan không?',
            default: false,
            hint: 'Việc này xóa toàn bộ lịch sử của các hạng mục bị ảnh hưởng, như thể dữ liệu chưa từng tồn tại.',
        );

        // Step 8: Delete associated files?
        $deleteFiles = false;
        if ($deleteType !== 'none') {
            $deleteFiles = confirm(
                label: 'Có xóa cả ảnh và tệp upload liên quan không?',
                default: $deleteType === 'hard',
                hint: 'Xóa vĩnh viễn ảnh, avatar, chữ ký, EULA và tệp upload của nhật ký khỏi ổ đĩa.',
            );
        }

        // Step 9: Delete the companies themselves?
        $deleteCompanyType = 'keep';
        if (! empty($selectedCompanyIds)) {
            $deleteCompanyType = select(
                label: 'Có xóa cả các công ty đã chọn không?',
                options: [
                    'keep' => 'Giữ lại - không xóa công ty',
                    'soft' => 'Xóa mềm - chuyển công ty vào thùng rác (có thể khôi phục)',
                    'hard' => 'Xóa vĩnh viễn - không thể khôi phục',
                ],
                default: 'keep',
            );
        }

        // Step 10: Backup first?
        $doBackup = confirm(
            label: 'Có chạy sao lưu trước khi tiếp tục không?',
            default: true,
            hint: 'Rất khuyến nghị. Bản sao lưu sẽ lưu dạng backup-before-bulk-delete-cli-[datetime].zip',
        );

        // Step 11: Summary + final confirmation
        $this->line('');
        $this->line('  ════════════════════════════════════════════════════');
        $this->line('   SUMMARY OF ACTIONS');
        $this->line('  ════════════════════════════════════════════════════');
        $this->line("   Người quản trị: {$admin->first_name} {$admin->last_name} ({$admin->username})");
        $this->line('   Công ty:         '.implode(', ', $selectedCompanyNames));
        $this->line('   Loại hạng mục:   '.implode(', ', $selectedTypes));
        $this->line("   Chế độ xóa:      {$deleteType}");
        $this->line('   Thông báo:       '.($sendNotifications ? 'Có' : 'Không'));
        $this->line('   Xóa nhật ký:     '.($clearLogs ? 'Có' : 'Không'));
        $this->line('   Xóa tệp:         '.($deleteFiles ? 'Có' : 'Không'));
        $this->line('   Xóa công ty:     '.($deleteCompanyType === 'keep' ? 'Không' : ucfirst($deleteCompanyType).' delete'));
        $this->line('   Sao lưu trước:   '.($doBackup ? 'Có' : 'Không'));
        $this->line('   Chạy thử:        '.($dryRun ? 'Có' : 'Không'));
        $this->line('');
        $this->line('   Hạng mục sẽ xử lý:');
        foreach ($counts as $type => $count) {
            $this->line(sprintf('     %-14s %d', ucfirst($type).':', $count));
        }
        if ($skipAdminUser) {
            $this->line('   * Your user account will be skipped during user deletion.');
        }
        $this->line('  ════════════════════════════════════════════════════');
        $this->line('');

        // Step 10.5: Email report?
        $sendEmailReport = false;
        if ($admin->email) {
            $sendEmailReport = confirm(
                label: "Gửi báo cáo email tới {$admin->email}?",
                default: false,
                hint: 'Tóm tắt toàn bộ '.($dryRun ? 'dự kiến ' : '').'thao tác sẽ được gửi qua email cho bạn.',
            );
        }

        if (! $dryRun) {
            $confirmed = confirm(
                label: 'Bạn có chắc muốn tiếp tục? Không thể hoàn tác.',
                default: false,
            );

            if (! $confirmed) {
                info('Đã hủy. Chưa có thay đổi nào được thực hiện.');

                return 0;
            }
        }

        // Run backup if requested
        if ($doBackup && ! $dryRun) {
            $backupFilename = 'backup-before-bulk-delete-cli-'.now()->format('Y-m-d-H-i-s');
            info("Đang chạy sao lưu ({$backupFilename}.zip)...");
            $result = $this->callSilently('hsbit:backup', ['--filename' => $backupFilename]);
            if ($result === 0) {
                info("Sao lưu hoàn tất: {$backupFilename}.zip");
            } else {
                warning("Backup may have failed (exit code {$result}). Proceeding anyway.");
            }
        }

        // Step 11: Execute with progress bar
        $totalItems = array_sum($counts);
        $bar = $this->output->createProgressBar($totalItems > 0 ? $totalItems : 1);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage('Đang bắt đầu...');
        $bar->start();

        foreach ($selectedTypes as $type) {
            match ($type) {
                'assets' => $this->processAssets($selectedCompanyIds, $includeNullCompany, $sendNotifications, $admin, $dryRun, $deleteType, $clearLogs, $deleteFiles, $bar),
                'licenses' => $this->processLicenses($selectedCompanyIds, $includeNullCompany, $sendNotifications, $admin, $dryRun, $deleteType, $clearLogs, $deleteFiles, $bar),
                'accessories' => $this->processAccessories($selectedCompanyIds, $includeNullCompany, $sendNotifications, $admin, $dryRun, $deleteType, $clearLogs, $deleteFiles, $bar),
                'components' => $this->processComponents($selectedCompanyIds, $includeNullCompany, $sendNotifications, $admin, $dryRun, $deleteType, $clearLogs, $deleteFiles, $bar),
                'consumables' => $this->processConsumables($selectedCompanyIds, $includeNullCompany, $dryRun, $deleteType, $clearLogs, $deleteFiles, $bar),
                'users' => $this->processUsers($selectedCompanyIds, $includeNullCompany, $admin, $skipAdminUser, $dryRun, $deleteType, $clearLogs, $deleteFiles, $bar),
            };
        }

        $bar->setMessage('Hoàn tất.');
        $bar->finish();
        $this->line('');
        $this->line('');

        // Delete companies if requested
        if ($deleteCompanyType !== 'keep' && ! empty($selectedCompanyIds)) {
            $companies = Company::whereIn('id', $selectedCompanyIds)->get();
            foreach ($companies as $company) {
                if ($dryRun) {
                    $this->line("  [dry-run] Sẽ {$deleteCompanyType}-delete công ty {$company->name}");
                    $this->reportLines[] = "Sẽ {$deleteCompanyType}-delete công ty {$company->name}";
                } else {
                    if ($deleteCompanyType === 'soft') {
                        $company->delete();
                    } else {
                        $company->forceDelete();
                    }
                    // Remove any remaining pivot associations (e.g. the admin user who was
                    // skipped during user processing but is still a member of this company)
                    DB::table('company_user')->where('company_id', $company->id)->delete();
                    $this->reportLines[] = ucfirst($deleteCompanyType)."-deleted company {$company->name}";
                }
            }
        }

        if ($dryRun) {
            warning('Dry run complete — no changes were made.');
        } else {
            info('All actions completed successfully.');
        }

        if ($sendEmailReport && $admin->email) {
            Mail::to($admin->email)->send(new BulkDeleteReportMail(
                admin: $admin,
                dryRun: $dryRun,
                companyNames: $selectedCompanyNames,
                selectedTypes: $selectedTypes,
                deleteType: $deleteType,
                reportLines: $this->reportLines,
                runAt: now(),
            ));
            info("Đã gửi báo cáo tới {$admin->email}.");
        }

        return 0;
    }

    private function getCounts(array $types, array $companyIds, bool $includeNull): array
    {
        $counts = [];

        if (in_array('assets', $types)) {
            $counts['assets'] = $this->buildCompanyQuery(Asset::query(), $companyIds, $includeNull)->count();
        }
        if (in_array('licenses', $types)) {
            $counts['licenses'] = $this->buildCompanyQuery(License::query(), $companyIds, $includeNull)->count();
        }
        if (in_array('accessories', $types)) {
            $counts['accessories'] = $this->buildCompanyQuery(Accessory::query(), $companyIds, $includeNull)->count();
        }
        if (in_array('components', $types)) {
            $counts['components'] = $this->buildCompanyQuery(Component::query(), $companyIds, $includeNull)->count();
        }
        if (in_array('consumables', $types)) {
            $counts['consumables'] = $this->buildCompanyQuery(Consumable::query(), $companyIds, $includeNull)->count();
        }
        if (in_array('users', $types)) {
            $counts['users'] = $this->buildUserQuery($companyIds, $includeNull)->count();
        }

        return $counts;
    }

    private function buildCompanyQuery(Builder $query, array $companyIds, bool $includeNull): Builder
    {
        return $query->where(function (Builder $q) use ($companyIds, $includeNull) {
            if (! empty($companyIds)) {
                $q->whereIn('company_id', $companyIds);
            }
            if ($includeNull) {
                $method = ! empty($companyIds) ? 'orWhereNull' : 'whereNull';
                $q->{$method}('company_id');
            }
        });
    }

    private function buildUserQuery(array $companyIds, bool $includeNull): Builder
    {
        return User::query()
            ->where('activated', 1)
            ->where(function (Builder $q) use ($companyIds, $includeNull) {
                if (! empty($companyIds)) {
                    $q->whereHas('companies', fn (Builder $sub) => $sub->whereIn('companies.id', $companyIds));
                }
                if ($includeNull) {
                    $method = ! empty($companyIds) ? 'orWhereDoesntHave' : 'whereDoesntHave';
                    $q->{$method}('companies');
                }
            });
    }

    private function processAssets(
        array $companyIds,
        bool $includeNull,
        bool $sendNotifications,
        User $admin,
        bool $dryRun,
        string $deleteType,
        bool $clearLogs,
        bool $deleteFiles,
        ProgressBar $bar,
    ): void {
        $assets = $this->buildCompanyQuery(Asset::query(), $companyIds, $includeNull)->get();

        foreach ($assets as $asset) {
            $bar->setMessage("Tài sản: {$asset->asset_tag}");

            if ($asset->assignedTo) {
                if ($dryRun) {
                    $this->line("  [dry-run] Sẽ thu hồi tài sản {$asset->asset_tag} từ {$asset->assignedTo->name}");
                    $this->reportLines[] = "Sẽ thu hồi tài sản {$asset->asset_tag} (đang gán cho {$asset->assignedTo->name})";
                } else {
                    $target = $asset->assignedTo;
                    $checkinAt = now()->format('Y-m-d H:i:s');
                    $originalValues = $asset->getRawOriginal();

                    if ($sendNotifications) {
                        event(new CheckoutableCheckedIn($asset, $target, $admin, self::CHECKIN_NOTE, $checkinAt, $originalValues));
                        DB::table('assets')->where('id', $asset->id)->update(['assigned_to' => null, 'assigned_type' => null]);
                    } else {
                        DB::table('assets')->where('id', $asset->id)->update(['assigned_to' => null, 'assigned_type' => null]);
                        $asset->logCheckin($target, self::CHECKIN_NOTE, $checkinAt, $originalValues);
                    }

                    $this->reportLines[] = "Đã thu hồi tài sản {$asset->asset_tag} từ {$target->name}";
                    $asset->licenseseats()->update(['assigned_to' => null]);

                    CheckoutAcceptance::where('checkoutable_type', Asset::class)
                        ->where('checkoutable_id', $asset->id)
                        ->whereNull('accepted_at')
                        ->whereNull('declined_at')
                        ->forceDelete();
                }
            }

            if (! $dryRun) {
                // Collect action log file paths before logs may be cleared
                $actionLogPaths = $deleteFiles
                    ? $asset->assetlog()->whereKhôngtNull('filename')->get()
                        ->map(fn (Actionlog $log) => $log->uploads_file_path())
                        ->filter()
                        ->values()
                        ->toArray()
                    : [];

                // Delete checkout acceptance files, then hard-remove all acceptances
                if ($deleteFiles) {
                    CheckoutAcceptance::where('checkoutable_type', Asset::class)
                        ->where('checkoutable_id', $asset->id)
                        ->get()
                        ->each(fn (CheckoutAcceptance $ca) => $this->deleteAcceptanceFiles($ca));
                }
                CheckoutAcceptance::where('checkoutable_type', Asset::class)
                    ->where('checkoutable_id', $asset->id)
                    ->forceDelete();

                // Hard-delete-only cleanup: maintenance records, accessory checkouts to this
                // asset, and any other assets that were assigned to this one
                $maintenanceImages = [];
                if ($deleteType === 'hard') {
                    if ($deleteFiles) {
                        $maintenanceImages = $asset->maintenances()
                            ->whereKhôngtNull('image')
                            ->pluck('image')
                            ->toArray();
                    }
                    $asset->maintenances()->forceDelete();
                    AccessoryCheckout::where('assigned_to', $asset->id)
                        ->where('assigned_type', Asset::class)
                        ->delete();
                    DB::table('assets')
                        ->where('assigned_to', $asset->id)
                        ->where('assigned_type', Asset::class)
                        ->update(['assigned_to' => null, 'assigned_type' => null]);
                }

                match ($deleteType) {
                    'soft' => $asset->delete(),
                    'hard' => $asset->forceDelete(),
                    default => null,
                };

                if ($deleteType !== 'none') {
                    $this->reportLines[] = ucfirst($deleteType)."-deleted asset {$asset->asset_tag}";
                }

                if ($clearLogs) {
                    $asset->assetlog()->forceDelete();
                }

                if ($deleteFiles) {
                    if ($asset->image) {
                        $this->deleteStorageFile('public', app('assets_upload_path').$asset->image);
                    }
                    foreach ($maintenanceImages as $img) {
                        $this->deleteStorageFile('public', app('maintenances_upload_path').$img);
                    }
                    foreach ($actionLogPaths as $path) {
                        $this->deleteStorageFile('local', $path);
                    }
                }
            } elseif ($deleteType !== 'none') {
                $this->line("  [dry-run] Sẽ {$deleteType}-delete tài sản {$asset->asset_tag}");
                $this->reportLines[] = "Sẽ {$deleteType}-delete tài sản {$asset->asset_tag}";
            }

            $bar->advance();
        }
    }

    private function processLicenses(
        array $companyIds,
        bool $includeNull,
        bool $sendNotifications,
        User $admin,
        bool $dryRun,
        string $deleteType,
        bool $clearLogs,
        bool $deleteFiles,
        ProgressBar $bar,
    ): void {
        $licenses = $this->buildCompanyQuery(License::query(), $companyIds, $includeNull)->get();

        foreach ($licenses as $license) {
            $bar->setMessage("License: {$license->name}");

            $seats = LicenseSeat::where('license_id', $license->id)
                ->where(fn ($q) => $q->whereKhôngtNull('assigned_to')->orWhereKhôngtNull('asset_id'))
                ->get();

            foreach ($seats as $seat) {
                $target = $seat->assigned_to ? $seat->user : $seat->asset;

                if ($dryRun) {
                    $this->line("  [dry-run] Sẽ thu hồi ghế license của {$license->name} từ ".($target?->name ?? $target?->asset_tag ?? 'không rõ'));
                    $this->reportLines[] = "Sẽ thu hồi ghế license của {$license->name} từ ".($target?->name ?? $target?->asset_tag ?? 'không rõ');
                } else {
                    $seat->assigned_to = null;
                    $seat->asset_id = null;
                    $seat->save();

                    $this->reportLines[] = "Đã thu hồi ghế license của {$license->name} từ ".($target?->name ?? $target?->asset_tag ?? 'không rõ');

                    if ($target) {
                        if ($sendNotifications) {
                            event(new CheckoutableCheckedIn($seat, $target, $admin, self::CHECKIN_NOTE));
                        } else {
                            $seat->logCheckin($target, self::CHECKIN_NOTE);
                        }
                    }
                }
            }

            if (! $dryRun) {
                // Collect action log file paths before logs may be cleared
                $actionLogPaths = $deleteFiles
                    ? $license->assetlog()->whereKhôngtNull('filename')->get()
                        ->map(fn (Actionlog $log) => $log->uploads_file_path())
                        ->filter()
                        ->values()
                        ->toArray()
                    : [];

                if ($deleteType === 'soft') {
                    $license->licenseseats()->delete();
                    $license->delete();
                    $this->reportLines[] = "Đã xóa mềm license {$license->name}";
                } elseif ($deleteType === 'hard') {
                    $seatIds = $license->licenseseats()->pluck('id');
                    if ($deleteFiles) {
                        CheckoutAcceptance::where('checkoutable_type', LicenseSeat::class)
                            ->whereIn('checkoutable_id', $seatIds)
                            ->get()
                            ->each(fn (CheckoutAcceptance $ca) => $this->deleteAcceptanceFiles($ca));
                    }
                    CheckoutAcceptance::where('checkoutable_type', LicenseSeat::class)
                        ->whereIn('checkoutable_id', $seatIds)
                        ->forceDelete();
                    $license->licenseseats()->forceDelete();
                    DB::table('kits_licenses')->where('license_id', $license->id)->delete();
                    $license->forceDelete();
                    $this->reportLines[] = "Đã xóa vĩnh viễn license {$license->name}";
                }

                if ($clearLogs) {
                    $license->assetlog()->forceDelete();
                }

                if ($deleteFiles) {
                    foreach ($actionLogPaths as $path) {
                        $this->deleteStorageFile('local', $path);
                    }
                }
            } elseif ($deleteType !== 'none') {
                $this->line("  [dry-run] Sẽ {$deleteType}-delete license {$license->name}");
                $this->reportLines[] = "Sẽ {$deleteType}-delete license {$license->name}";
            }

            $bar->advance();
        }
    }

    private function processAccessories(
        array $companyIds,
        bool $includeNull,
        bool $sendNotifications,
        User $admin,
        bool $dryRun,
        string $deleteType,
        bool $clearLogs,
        bool $deleteFiles,
        ProgressBar $bar,
    ): void {
        $accessories = $this->buildCompanyQuery(Accessory::query(), $companyIds, $includeNull)->get();

        foreach ($accessories as $accessory) {
            $bar->setMessage("Phụ kiện: {$accessory->name}");

            $checkouts = AccessoryCheckout::where('accessory_id', $accessory->id)->get();

            foreach ($checkouts as $checkout) {
                $target = $checkout->assignedTo;

                if ($dryRun) {
                    $this->line("  [dry-run] Sẽ thu hồi phụ kiện {$accessory->name} từ ".($target?->name ?? 'không rõ'));
                    $this->reportLines[] = "Sẽ thu hồi phụ kiện {$accessory->name} từ ".($target?->name ?? 'không rõ');
                } else {
                    $checkinAt = now()->format('Y-m-d H:i:s');
                    $checkout->delete();

                    $this->reportLines[] = "Đã thu hồi phụ kiện {$accessory->name} từ ".($target?->name ?? 'không rõ');

                    if ($target) {
                        if ($sendNotifications) {
                            event(new CheckoutableCheckedIn($accessory, $target, $admin, self::CHECKIN_NOTE, $checkinAt));
                        } else {
                            $accessory->logCheckin($target, self::CHECKIN_NOTE, $checkinAt);
                        }
                    }
                }
            }

            if (! $dryRun) {
                // Collect action log file paths before logs may be cleared
                $actionLogPaths = $deleteFiles
                    ? $accessory->assetlog()->whereKhôngtNull('filename')->get()
                        ->map(fn (Actionlog $log) => $log->uploads_file_path())
                        ->filter()
                        ->values()
                        ->toArray()
                    : [];

                if ($clearLogs) {
                    $accessory->assetlog()->forceDelete();
                }

                if ($deleteType === 'hard') {
                    DB::table('kits_accessories')->where('accessory_id', $accessory->id)->delete();
                }

                match ($deleteType) {
                    'soft' => $accessory->delete(),
                    'hard' => $accessory->forceDelete(),
                    default => null,
                };

                if ($deleteType !== 'none') {
                    $this->reportLines[] = ucfirst($deleteType)."-deleted accessory {$accessory->name}";
                }

                if ($deleteFiles) {
                    if ($accessory->image) {
                        $this->deleteStorageFile('public', app('accessories_upload_path').$accessory->image);
                    }
                    foreach ($actionLogPaths as $path) {
                        $this->deleteStorageFile('local', $path);
                    }
                }
            } elseif ($deleteType !== 'none') {
                $this->line("  [dry-run] Sẽ {$deleteType}-delete phụ kiện {$accessory->name}");
                $this->reportLines[] = "Sẽ {$deleteType}-delete phụ kiện {$accessory->name}";
            }

            $bar->advance();
        }
    }

    private function processComponents(
        array $companyIds,
        bool $includeNull,
        bool $sendNotifications,
        User $admin,
        bool $dryRun,
        string $deleteType,
        bool $clearLogs,
        bool $deleteFiles,
        ProgressBar $bar,
    ): void {
        $components = $this->buildCompanyQuery(Component::query(), $companyIds, $includeNull)->get();

        foreach ($components as $component) {
            $bar->setMessage("Linh kiện: {$component->name}");

            $assignments = DB::table('components_assets')
                ->where('component_id', $component->id)
                ->get();

            foreach ($assignments as $assignment) {
                $asset = Asset::find($assignment->asset_id);

                if ($dryRun) {
                    $this->line("  [dry-run] Sẽ thu hồi linh kiện {$component->name} từ tài sản ".($asset?->asset_tag ?? 'không rõ'));
                    $this->reportLines[] = "Sẽ thu hồi linh kiện {$component->name} từ tài sản ".($asset?->asset_tag ?? 'không rõ');
                } else {
                    $checkinAt = now()->format('Y-m-d H:i:s');
                    DB::table('components_assets')->where('id', $assignment->id)->delete();

                    $this->reportLines[] = "Đã thu hồi linh kiện {$component->name} từ tài sản ".($asset?->asset_tag ?? 'không rõ');

                    if ($asset) {
                        if ($sendNotifications) {
                            event(new CheckoutableCheckedIn($component, $asset, $admin, self::CHECKIN_NOTE, $checkinAt));
                        } else {
                            $component->logCheckin($asset, self::CHECKIN_NOTE, $checkinAt);
                        }
                    }
                }
            }

            if (! $dryRun) {
                // Collect action log file paths before logs may be cleared
                $actionLogPaths = $deleteFiles
                    ? $component->assetlog()->whereKhôngtNull('filename')->get()
                        ->map(fn (Actionlog $log) => $log->uploads_file_path())
                        ->filter()
                        ->values()
                        ->toArray()
                    : [];

                if ($clearLogs) {
                    $component->assetlog()->forceDelete();
                }

                match ($deleteType) {
                    'soft' => $component->delete(),
                    'hard' => $component->forceDelete(),
                    default => null,
                };

                if ($deleteType !== 'none') {
                    $this->reportLines[] = ucfirst($deleteType)."-deleted component {$component->name}";
                }

                if ($deleteFiles) {
                    if ($component->image) {
                        $this->deleteStorageFile('public', app('components_upload_path').$component->image);
                    }
                    foreach ($actionLogPaths as $path) {
                        $this->deleteStorageFile('local', $path);
                    }
                }
            } elseif ($deleteType !== 'none') {
                $this->line("  [dry-run] Sẽ {$deleteType}-delete linh kiện {$component->name}");
                $this->reportLines[] = "Sẽ {$deleteType}-delete linh kiện {$component->name}";
            }

            $bar->advance();
        }
    }

    private function processConsumables(
        array $companyIds,
        bool $includeNull,
        bool $dryRun,
        string $deleteType,
        bool $clearLogs,
        bool $deleteFiles,
        ProgressBar $bar,
    ): void {
        $consumables = $this->buildCompanyQuery(Consumable::query(), $companyIds, $includeNull)->get();

        foreach ($consumables as $consumable) {
            $bar->setMessage("Vật tư: {$consumable->name}");

            if (! $dryRun) {
                // Collect action log file paths before logs may be cleared
                $actionLogPaths = $deleteFiles
                    ? $consumable->assetlog()->whereKhôngtNull('filename')->get()
                        ->map(fn (Actionlog $log) => $log->uploads_file_path())
                        ->filter()
                        ->values()
                        ->toArray()
                    : [];

                if ($clearLogs) {
                    $consumable->assetlog()->forceDelete();
                }

                if ($deleteType === 'hard') {
                    DB::table('kits_consumables')->where('consumable_id', $consumable->id)->delete();
                }

                match ($deleteType) {
                    'soft' => $consumable->delete(),
                    'hard' => $consumable->forceDelete(),
                    default => null,
                };

                if ($deleteType !== 'none') {
                    $this->reportLines[] = ucfirst($deleteType)."-deleted consumable {$consumable->name}";
                }

                if ($deleteFiles) {
                    if ($consumable->image) {
                        $this->deleteStorageFile('public', app('consumables_upload_path').$consumable->image);
                    }
                    foreach ($actionLogPaths as $path) {
                        $this->deleteStorageFile('local', $path);
                    }
                }
            } elseif ($deleteType !== 'none') {
                $this->line("  [dry-run] Sẽ {$deleteType}-delete vật tư {$consumable->name}");
                $this->reportLines[] = "Sẽ {$deleteType}-delete vật tư {$consumable->name}";
            }

            $bar->advance();
        }
    }

    private function processUsers(
        array $companyIds,
        bool $includeNull,
        User $admin,
        bool $skipAdminUser,
        bool $dryRun,
        string $deleteType,
        bool $clearLogs,
        bool $deleteFiles,
        ProgressBar $bar,
    ): void {
        $users = $this->buildUserQuery($companyIds, $includeNull)->get();

        foreach ($users as $user) {
            if ($skipAdminUser && $user->id === $admin->id) {
                continue;
            }

            $bar->setMessage("Người dùng: {$user->username}");

            // If real companies were selected, check whether this user also belongs to
            // companies outside the selected scope. If so, only remove the selected-company
            // associations and skip full deletion to avoid orphaning them from their other companies.
            if (! empty($companyIds)) {
                // Pivot is the authoritative source of membership; the legacy
                // users.company_id scalar is no longer consulted.
                $allUserCompanyIds = array_values(array_unique(array_filter(
                    $user->companies()->pluck('companies.id')->toArray(),
                )));
                $outsideCompanyIds = array_values(array_diff($allUserCompanyIds, $companyIds));

                if (! empty($outsideCompanyIds)) {
                    $outsideNames = Company::whereIn('id', $outsideCompanyIds)->pluck('name')->implode(', ');

                    if ($dryRun) {
                        $this->line("  [dry-run] Sẽ gỡ liên kết một phần người dùng {$user->username} (cũng thuộc: {$outsideNames})");
                        $this->reportLines[] = "Sẽ gỡ liên kết một phần người dùng {$user->username} - cũng thuộc: {$outsideNames}";
                    } else {
                        $user->companies()->detach($companyIds);
                        $user->syncLegacyCompanyIdMirror();
                        warning("  Skipped full deletion of {$user->username}: they also belong to {$outsideNames}. Removed selected company associations only.");
                        $this->reportLines[] = "Partially disassociated user {$user->username} — also belongs to: {$outsideNames}. Full deletion skipped.";
                    }

                    $bar->advance();

                    continue;
                }
            }

            if (! $dryRun) {
                // Collect file paths and acceptance records before deleting pivot data
                $acceptancesToDelete = $deleteFiles
                    ? CheckoutAcceptance::where('assigned_to_id', $user->id)->get()
                    : collect();

                $actionLogPaths = $deleteFiles
                    ? Actionlog::where('item_type', User::class)
                        ->where('item_id', $user->id)
                        ->where('action_type', 'uploaded')
                        ->whereKhôngtNull('filename')
                        ->get()
                        ->map(fn (Actionlog $log) => $log->uploads_file_path())
                        ->filter()
                        ->values()
                        ->toArray()
                    : [];

                // Clear pivot/assignment data that will orphan on deletion
                LicenseSeat::where('assigned_to', $user->id)->update(['assigned_to' => null]);
                AccessoryCheckout::where('assigned_to', $user->id)
                    ->where('assigned_type', User::class)
                    ->delete();
                DB::table('consumables_users')->where('assigned_to', $user->id)->delete();
                CheckoutAcceptance::where('assigned_to_id', $user->id)->forceDelete();
                if ($deleteType === 'hard') {
                    DB::table('company_user')->where('user_id', $user->id)->delete();
                }

                if ($clearLogs) {
                    $user->userlog()->forceDelete();
                }

                match ($deleteType) {
                    'soft' => $user->delete(),
                    'hard' => $user->forceDelete(),
                    default => null,
                };

                if ($deleteType !== 'none') {
                    $this->reportLines[] = ucfirst($deleteType)."-deleted user {$user->username}";
                }

                if ($deleteFiles) {
                    if ($user->avatar) {
                        $this->deleteStorageFile('public', app('users_upload_path').$user->avatar);
                    }
                    $acceptancesToDelete->each(fn (CheckoutAcceptance $ca) => $this->deleteAcceptanceFiles($ca));
                    foreach ($actionLogPaths as $path) {
                        $this->deleteStorageFile('local', $path);
                    }
                }
            } elseif ($deleteType !== 'none') {
                $this->line("  [dry-run] Sẽ {$deleteType}-delete người dùng {$user->username}");
                $this->reportLines[] = "Sẽ {$deleteType}-delete người dùng {$user->username}";
            }

            $bar->advance();
        }
    }

    private function deleteStorageFile(string $disk, ?string $path): void
    {
        if (! $path) {
            return;
        }
        try {
            $storage = $disk === 'public'
                ? Storage::disk('public')
                : Storage::disk(config('filesystems.default'));
            if ($storage->exists($path)) {
                $storage->delete($path);
            }
        } catch (\Exception $e) {
            Log::warning("Could not delete file {$path}: ".$e->getMessage());
        }
    }

    private function deleteAcceptanceFiles(CheckoutAcceptance $acceptance): void
    {
        if ($acceptance->signature_filename) {
            $this->deleteStorageFile('local', 'private_uploads/signatures/'.$acceptance->signature_filename);
        }
        if ($acceptance->stored_eula_file) {
            $this->deleteStorageFile('local', 'private_uploads/eula-pdfs/'.$acceptance->stored_eula_file);
        }
    }
}
