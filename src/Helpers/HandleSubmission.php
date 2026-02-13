<?php namespace AltDesign\AltAkismet\Helpers;

use Illuminate\Support\Facades\Storage;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Filesystem\Manager;

class HandleSubmission
{
    public $manager;

    public function __construct(){
        // New up Stat File Manager
        $this->manager = new Manager();

        // Check Akismet folder exists
        if (!$this->manager->disk()->exists('content/alt-akismet')) {
            $this->manager->disk()->makeDirectory('content/alt-akismet');
        }
    }

    // Create our own version of the submission
    public static function moveSubmissionToAltAkismet($submission, $type)
    {
        $manager = new Manager();
        $id = $submission->id ?? $submission->id();
        $data = array_merge($submission->data()->toArray(), [
            'alt_akismet_id' => $id,
            'alt_akismet' => $submission->alt_akismet ?? $type,
            'alt_akismet_name' => $submission->alt_akismet_name ?? '',
            'alt_akismet_email' => $submission->alt_akismet_email ?? '',
            'alt_akismet_content' => $submission->alt_akismet_content ?? '',
            'alt_form_slug' => $submission->alt_form_slug ?? $submission->form()->handle(),
        ]);
        $manager->disk()->put('content/alt-akismet/'.$id.'.yaml', Yaml::dump($data));
    }

    public static function updateSubmissionInAltAkismet($id, $type)
    {
        // update the type in the submission
        $manager = new Manager();
        $submission = $manager->disk()->get('content/alt-akismet/'.$id.'.yaml'); // We'll always have this one
        $submission = Yaml::parse($submission);
        $submission['alt_akismet'] = $type;
        $manager->disk()->put('content/alt-akismet/'.$id.'.yaml', Yaml::dump($submission));

        // send report to akismet - TODO, they can wait for now :)

        // If we're marking spam - remove the real submission from the forms
        if($type == 'spam') {
            if (!$manager->disk()->exists('storage/forms/'.$submission['alt_form_slug'])) {
                $manager->disk()->makeDirectory('storage/forms/'.$submission['alt_form_slug']);
            }

            unlink(storage_path('/forms/'.$submission['alt_form_slug'].'/'.$id.'.yaml'));
        } else {
            // If we're marking ham - add the real submission back to the forms
            File::copy(base_path('/content/alt-akismet/'.$id.'.yaml'), storage_path('/forms/'.$submission['alt_form_slug'].'/'.$id.'.yaml'));
        }
    }
}
