<?php

namespace App\Console\Commands;

use App\Models\CustomField;
use Illuminate\Console\Command;

class ReEncodeCustomFieldNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hsbit:regenerate-fieldnames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo lại tên cột cho trường tùy chỉnh. Thường chỉ cần khi nâng cấp PHP làm thay đổi cách chuyển đổi Unicode.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * All three of these things must match for the custom fields system to work as expected:
     *
     * - what the system thinks the output of $field->convertUnicodeDbSlug() is
     * - the actual db_column name in the customfields table
     * - the physical column name that was created on the assets table
     *
     * For some people who upgraded their version of PHP, the unicode converter now behaves
     * differently in than it did when their custom fields were first created, specifically as it
     * relates to handling slashes, ampersands, etc. This can result in the field names no longer
     * matching up, as an older version of the PHP extension simply dropped slashes, etc, while the
     * newer version of the PHP extension will convert them to underscores.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->confirm('Thao tác này sẽ tạo lại toàn bộ tên trường cơ sở dữ liệu của trường tùy chỉnh. VIỆC NÀY SẼ THAY ĐỔI SCHEMA VÀ KHÔNG NÊN THỰC HIỆN NẾU CHƯA SAO LƯU. Bạn có muốn tiếp tục?')) {

            /** Get all of the custom fields */
            $fields = CustomField::get();

            $asset_columns = \DB::getSchemaBuilder()->getColumnListing('assets');
            $custom_field_columns = [];

            /** Loop through the columns on the assets table */
            foreach ($asset_columns as $asset_column) {

                /** Add ones that start with _hsbit_ to an array for handling */
                if (strpos($asset_column, '_hsbit_') === 0) {

                    /**
                     * Get the ID of the custom field based on the fieldname.
                     * For example, in _hsbit_mac_address_1, we grab the 1 because we know
                     * that's the ID of the custom field that created the column.
                     * Then use that ID as the array key for use comparing the actual assets field name
                     * and the db_column value from the custom fields table.
                     */
                    $last_part = substr(strrchr($asset_column, '_hsbit_'), 1);
                    $custom_field_columns[$last_part] = $asset_column;

                }
            }

            foreach ($fields as $field) {
                $this->info($field->name.' ('.$field->id.') column should be '.$field->convertUnicodeDbSlug());

                /** The assets table has the column it should have, all is well */
                if ($field->db_column == $field->convertUnicodeDbSlug() && \Schema::hasColumn('assets', $field->convertUnicodeDbSlug())) {
                    $this->info('-- ✓ Trường này tồn tại trong bảng assets và giá trị db_column khớp với bảng custom_fields.');

                    /**
                     * There is a mismatch between the fieldname on the assets table and
                     * what $field->convertUnicodeDbSlug() is *now* expecting.
                     */
                } else {

                    if ($field->db_column != $field->convertUnicodeDbSlug()) {
                        $this->error('-- ✘ Field mismatch: '.$field->name.' value should be '.$field->convertUnicodeDbSlug().' but is '.$field->db_column.' in the custom_fields table');

                    } else {
                        $this->error('-- ✘ Field mismatch: '.$field->name.' column should be '.$field->convertUnicodeDbSlug().' but is '.$custom_field_columns[$field->id].' on the assets table.');

                    }

                    /** Make sure the custom_field_columns array has the ID */
                    if (array_key_exists($field->id, $custom_field_columns)) {

                        /**
                         * Update the asset schema to the corrected fieldname that will be recognized by the
                         *  system elsewhere that we use $field->convertUnicodeDbSlug()
                         */
                        $this->info('-- ✓ Updating field from '.$field->db_column.' to '.$field->convertUnicodeDbSlug().' in the assets table');
                        \Schema::table('assets', function ($table) use ($custom_field_columns, $field) {
                            $table->renameColumn($custom_field_columns[$field->id], $field->convertUnicodeDbSlug());
                        });

                        $this->info('-- ✓ Updating field from '.$field->db_column.' to '.$field->convertUnicodeDbSlug().' in the custom fields table');

                        $field->db_column = $field->convertUnicodeDbSlug();
                        $field->save();

                    } else {
                        $this->warn('-- ✘ CẢNH BÁO: Không có trường nào trong bảng assets kết thúc bằng  '.$field->id.'. Có thể cần kiểm tra sâu hơn và schema có thể đã bị chỉnh sửa thủ công.');
                    }
                }

                /** Update the db_column property in the custom fields table, just in case it doesn't match the other
                 * things.
                 */
                $field->db_column = $field->convertUnicodeDbSlug();
                $field->save();
            }
        }
    }
}
