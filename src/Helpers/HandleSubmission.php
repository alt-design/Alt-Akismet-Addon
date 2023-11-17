<?php namespace AltDesign\AltAkismet\Helpers;

use Illuminate\Support\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Filesystem\Manager;

class HandleSubmission
{

    public $manager;

    public function __construct(){
        // New up Stat File Manager
        $this->manager = new Manager();

        // Check redirect folder exists
        if (!$this->manager->disk()->exists('content/alt-akismet')) {
            $this->manager->disk()->makeDirectory('content/alt-akismet');
        }
    }

    // this is running too soon, needs to be after save
    public static function moveSubmissionToAltAkismet($submission, $type)
    {
        $handle = $submission->form->handle();
        $sourcePath = storage_path('/forms/'.$handle);
        $destinationPath = base_path('/content/alt-akismet/');

        $files = File::allFiles($sourcePath);
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $latestFile = reset($files);

        $filename = pathinfo($latestFile, PATHINFO_FILENAME);
        $destinationFile = $destinationPath . $filename . '.yaml';
        // delete from storage if spam ---- need to wait until it's saved to delete ???
        File::copy($latestFile, $destinationFile);

    }

    public static function updateSubmissionInAltAkismet($id, $type)
    {
        // update the type in the submission
        // send report to akismet - TODO
        // add or remove from form submission - TODO
    }
}


/*
On boot get config/something to get key - need to auth key before calling methods - DONE
Listen for Form Submitted (not Form Saved) - DONE
Comment check through akismet - DONE
-- forms will need to use set handles to match the data
-- name (to check name)
-- email (to check email)
-- content (to check message)
(inc tests here with admin and guaranteed spam)
List all entries in Alt Akismet so can report spam/ham - DONE
-------
Copying file needs to happen when saved
Report spam action - needs to submit to akismet and delete entry from submissions (but keep in alt akismet) - button change to report Ham - TODO
Report ham action - needs to submit to akismet and add entry to submissions (this is why we copy all to alt akismet incase of false posi) - button change to report Spam - TODO
Delete Spam from form results to only show genuine results- TODO
Check it works with just name and email (as newletter signup) - TODO
Read Me - guranteed spam test: name=akismet-guaranteed-spam or email=akismet-guaranteed-spam@example.com -TODO
Remove is_test=1 param when all done - TODO
Amend user agent from wp?
*/

