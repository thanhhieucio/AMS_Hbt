<?php

namespace App\Http\Requests;

class SettingsFirebaseSourceRequest extends Request
{
    /**
     * Never flash the raw service account key back into the session on validation failure.
     */
    protected $dontFlash = ['service_account_base64'];

    /**
     * Keep validation errors separate from the rest of the settings page.
     */
    protected $errorBag = 'firebase_source';

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Firebase project IDs are lowercase letters, digits and hyphens, 6-30 chars.
            'firebase_project_id' => ['required', 'string', 'regex:/^[a-z][a-z0-9-]{4,28}[a-z0-9]$/'],
            // Firestore collection id: no slashes, not "." or "..", not matching __*__.
            'firestore_students_collection' => ['required', 'string', 'max:255', 'regex:/^(?!__.*__$)[A-Za-z0-9_-]+$/'],
            // Left blank to keep the service account key already stored.
            'service_account_base64' => ['nullable', 'string'],
        ];
    }
}
