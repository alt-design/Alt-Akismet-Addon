<?php namespace AltDesign\AltAkismet\Http\Controllers;

use Illuminate\Http\Request;
use AltDesign\AltAkismet\Helpers\Data;
use AltDesign\AltAkismet\Helpers\HandleSubmission;
use Statamic\Fields\BlueprintRepository as Blueprint;
use Statamic\Facades\File;
use Statamic\Facades\YAML;

/**
 * Class AltAkismetController
 *
 * @package  AltDesign\AltAkismet
 * @author   Ben Harvey <ben@alt-design.net>, Natalie Higgins <natalie@alt-design.net>
 * @license  Copyright (C) Alt Design Limited - All Rights Reserved - licensed under the MIT license
 * @link     https://alt-design.net
 */
class AltAkismetController {

    /**
     *  Render the default options page.
     */
    public function index()
    {
        // Publish form
        // Get an array of values
        $data = new \AltDesign\AltAkismet\Helpers\Data('akismet');
        $values = $data->all();

        // Get a blueprint.
        $blueprint = with(new Blueprint)->setDirectory(__DIR__ . '/../../../resources/blueprints')->find('akismet');

        // Get a Fields object
        $fields = $blueprint->fields();
        // Add the values to the object
        $fields = $fields->addValues($values);
        // Pre-process the values.
        $fields = $fields->preProcess();

        return view('alt-akismet::index', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'data'      => $values,
        ]);
    }

    public function submission($submission)
    {
        // Publish form
        // Get an array of values
        $data = YAML::parse(File::get(base_path('content/alt-akismet/'.$submission . '.yaml')));
        $values = $data;

        // Get a blueprint.
        $blueprint = with(new Blueprint)->setDirectory(__DIR__ . '/../../../resources/blueprints')->find('akismet');

        // Get a Fields object
        $fields = $blueprint->fields();
        // Add the values to the object
        $fields = $fields->addValues($values);
        // Pre-process the values.
        $fields = $fields->preProcess();

        return view('alt-akismet::submission', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'data'      => $values,
        ]);
    }

    public function update(Request $request){
        $type = $request->type;
        $id = $request->id;

        $formSubmission = new HandleSubmission();
        $formSubmission->updateSubmissionInAltAkismet($id, $type);
    }
}
