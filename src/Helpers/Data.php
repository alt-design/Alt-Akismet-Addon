<?php namespace AltDesign\AltAkismet\Helpers;

use Illuminate\Support\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Filesystem\Manager;

class Data
{
    public $type;
    public $manager;
    public $currentFile;
    public $data = [];

    public function __construct($type)
    {
        $this->type = $type;

        // New up Stat File Manager
        $this->manager = new Manager();

        // Check redirect folder exists
        if (!$this->manager->disk()->exists('content/alt-akismet')) {
            $this->manager->disk()->makeDirectory('content/alt-akismet');
        }

        // Get all files in the redirects folder
        $allSubmissions = File::allFiles(app_path() . '/../content/alt-akismet');
        $allSubmissions = collect($allSubmissions)->sortByDesc(function ($file) {
                return $file->getCTime();
        });

        // Loop through and get the Data
        foreach ($allSubmissions as $submission) {;
            $data = Yaml::parse(File::get($submission));
            $data['alt_akismet_id'] = str_replace('.yaml', '', basename($submission));
            $this->data[] = $data;
        }
    }

    public function get($key)
    {
        if (!isset($this->data[$key])) {
            return null;
        }
        return $this->data[$key];
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;

        Yaml::dump($this->data, $this->currentFile);
    }

    public function all()
    {
        return $this->data;
    }

    public function setAll($data)
    {
        $this->data = $data;

        $this->manager->disk()->put('content/alt-akismet/' . base64_encode($data['from']) . '.yaml', Yaml::dump($this->data));
    }

}
