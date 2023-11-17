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
            'comment_author' => $data['name'],
            'comment_author_email' => $data['email'],
            'comment_content' => $data['content'],
        );
        $this->akismet_comment_check(env("ALT_AKISMET_API_KEY"), $data, $submission);
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
            '&comment_content=' . urlencode($data['comment_content']) .
            '&is_test=1';

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

        //add the comment check result
        if ('true' == $response[1]) {
            $submission->alt_akismet = "spam";
        } else {
            $submission->alt_akismet = "ham";
        }

        //need to move this to when form is saved - TODO
        /*$data = $submission->data();
        $formSubmission = new HandleSubmission();
        if ($data['alt_akismet'] == "ham") {
            $formSubmission->moveSubmissionToAltAkismet($submission, "ham");
        } else {
            $formSubmission->moveSubmissionToAltAkismet($submission, "spam");
        }*/
    }
}
