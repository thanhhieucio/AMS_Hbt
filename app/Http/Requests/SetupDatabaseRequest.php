<?php

namespace App\Http\Requests;

class SetupDatabaseRequest extends Request
{
    /**
     * Never flash the raw password back into the session on validation failure.
     */
    protected $dontFlash = ['db_password'];

    /**
     * Keep validation errors separate from the rest of Step 1's page so the
     * PDO connection-test error (added manually in the controller) lands here too.
     */
    protected $errorBag = 'database';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'db_connection' => 'required|in:mysql,pgsql',
            'db_host' => 'required|string|max:255',
            'db_port' => 'required|integer|min:1|max:65535',
            // Restricted to safe SQL identifier characters: these values may be used verbatim
            // to CREATE DATABASE / CREATE USER when auto-provisioning a brand-new MySQL user.
            'db_database' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_]+$/'],
            'db_username' => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9_]+$/'],
            'db_password' => 'nullable|string|max:255',
            'db_sslmode' => 'nullable|boolean',
        ];
    }
}
