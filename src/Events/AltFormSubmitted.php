<?php namespace AltDesign\AltAkismet\Events;

use Statamic\Events;
use AltDesign\AltAkismet\Helpers\HandleSubmission;

class AltFormSubmitted
{
    protected $events = [
        Events\FormSubmitted::class => 'commentCheck',
    ];

    public function subscribe($events)
    {
        $events->listen(Events\FormSubmitted::class, self::class . '@' . 'commentCheck');
    }

    /**
     * We're trying to be clever here, and at least attempt to guess which field is the name.
     *
     * @param $data
     * @return void
     */
    public function attemptGetName($data)
    {
        // Default to the first key, as name is _usually_ the first field
        $data = $data->toArray();
        $name = $data[array_keys($data)[0]];

        // Check if there's a field called name
        if (isset($data['name'])) {
            $name = $data['name'];
        }

        // Check if there's a field called first_name
        if (isset($data['first_name'])) {
            $name = $data['first_name'];
        }

        // Check if there's a field called last_name
        if (isset($data['last_name'])) {
            // If there's a first name, append, if not, set
            if (isset($data['first_name'])) {
                $name .= ' ' . $data['last_name'];
            } else {
                $name = $data['last_name'];
            }
        }

        return $name;
    }

    /**
     * Same as above, try and guess the email field.
     *
     * @param $data
     * @return void
     */
    public function attemptGetEmail($data)
    {
        // It's potentially the second field? Better than nothing??
        $data = $data->toArray();
        $email = $data[array_keys($data)[1] ?? ''] ?? '';

        // Check if there's a field called email
        if (isset($data['email'])) {
            $email = $data['email'];
        }

        // Check if there's a field called email_address
        if (isset($data['email_address'])) {
            $email = $data['email_address'];
        }

        // Loop through, looking for the @ symbol and domain?
        foreach($data as $item) {
            if(is_array($item)) {
                continue;
            }
            if (strpos($item, '@') !== false && strpos($item, '.') !== false) {
                $email = $item;
                break;
            }
        }

        return $email;
    }

    /**
     * Slightly trickier here, but same concept
     *
     * @param $data
     * @param $fields
     * @return void
     */
    public function attemptGetContent($data, $fields)
    {
        $data = $data->toArray();

        // Pull the field types - cross-reference with the data, look for textarea
        $fieldTypes = $fields->map(function($field) {
            return $field->type();
        });

        // If there's a textarea, use that
        if ($fieldTypes->contains('textarea')) {
            $content = $data[$fieldTypes->search('textarea')];
        } else {
            $content = $data[array_keys($data)[0]];

            if (isset($data['message'])) {
                $content = $data['message'];
            }

            if (isset($data['comment'])) {
                $content = $data['comment'];
            }

            if (isset($data['content'])) {
                $content = $data['content'];
            }
        }

        return $content;
    }

    public function commentCheck($event)
    {
        $submission = $event->submission;
        $data = $event->submission->data();

        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'];
        $blog = urlencode($baseUrl);

        $data = array(
            'blog' => $blog, // Required
            'user_ip' => $_SERVER['REMOTE_ADDR'], // Required
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'referrer' => $_SERVER['HTTP_REFERER'],
            'permalink' => $_SERVER['HTTP_ORIGIN'],
            'comment_type' => 'contact-form',
            'comment_author' => $this->attemptGetName($data),
            'comment_author_email' => $this->attemptGetEmail($data),
            'comment_content' => $this->attemptGetContent($data, $event->submission->form()->fields()),
        );
        // Todo, check the returns here, for some reason they didn't seem to behave when returning true/false from the function??
        if($this->akismet_comment_check(env("ALT_AKISMET_API_KEY"), $data, $submission)) {
            return true;
        } else {
            return false;
        }
    }

    public function akismet_comment_check($api_key, $data, $submission)
    {
        $request = 'api_key=' . urlencode($api_key) . //Required
            '&blog=' . urlencode($data['blog']) . //Required
            '&user_ip=' . urlencode($data['user_ip']) . //Required
            '&user_agent=' . urlencode($data['user_agent']) .
            '&referrer=' . urlencode($data['referrer']) .
            '&permalink=' . urlencode($data['permalink']) .
            '&comment_type=' . urlencode($data['comment_type']) .
            '&comment_author=' . urlencode($data['comment_author']) .
            '&comment_author_email=' . urlencode($data['comment_author_email']) .
            '&comment_content=' . urlencode($data['comment_content']);
        $host = $http_host = 'rest.akismet.com';
        $path = '/1.1/comment-check';
        $port = 443;
        $akismet_ua = "Alt Akismet 1.0.0 | Akismet/3.1.7";
        $content_length = strlen($request);
        $http_request = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $http_request .= "Content-Length: {$content_length}\r\n";
        $http_request .= "User-Agent: {$akismet_ua}\r\n";
        $http_request .= "\r\n";
        $http_request .= $request;
        $response = '';

        if (false != ($fs = @fsockopen('ssl://' . $http_host, $port, $errno, $errstr, 10))) {
            fwrite($fs, $http_request);
            while (!feof($fs)) {
                $response .= fgets($fs, 1160); // One TCP-IP packet
            }
            fclose($fs);
            $response = explode("\r\n\r\n", $response, 2);
        }

        //add an ID
        $submission->id = uniqid();

        //add the comment check result.
        $submission->alt_akismet = $response[1] == "true" ? "spam" : "ham";

        // Add what we assumed the name, email and content to be, as this is what we checked.
        $submission->alt_akismet_name = $data['comment_author'];
        $submission->alt_akismet_email = $data['comment_author_email'];
        $submission->alt_akismet_content = $data['comment_content'];
        $submission->alt_form_slug = $submission->form()->handle();

        //need to move this to when form is saved - TODO
        $formSubmission = new HandleSubmission();
        if ($submission->alt_akismet == "ham") {
            $formSubmission->moveSubmissionToAltAkismet($submission, "ham");
            return true;
        } else {
            $formSubmission->moveSubmissionToAltAkismet($submission, "spam");
            return false; // Should disable notifs etc?
        }
    }
}
