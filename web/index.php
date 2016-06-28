<?php

require '../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$bot = new CU\LineBot();

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stderr',
));

$app->before(function (Request $request) use ($bot) {
    // Signature validation
    $request_body = $request->getContent();
    $signature = $request->headers->get('X-LINE-CHANNELSIGNATURE');
    if (!$bot->isValid($signature, $request_body)) {
        return new Response('Signature validation failed.', 400);
    }
});

$app->post('/callback', function (Request $request) use ($app, $bot) {
    // Let's hack from here!
    $body = json_decode($request->getContent(), true);

    foreach ($body['result'] as $obj) {
        $app['monolog']->addInfo(sprintf('obj: %s', json_encode($obj)));
        $from = $obj['content']['from'];
        $content = $obj['content'];

        if ($content['text']) {
            date_default_timezone_set('Asia/Tokyo');
            $now = date('Y-m-d H:i');
            $today = time();
            if (stristr($content['text'], 'gga') !== false) {
                if (stristr($content['text'], 'あと') !== false) {
                    $gga = strtotime('20161006');
                    $int = $gga - $today;
                    $day = ceil($int / (24 * 60 * 60));
                    $week = ceil($day / 7);
                    $format = 'GGAまであと%s日（%s週間）です。';
                    $message = sprintf($format, $now, $day, $week);
                    if ($day > 0) {
                        $bot->sendText($from,$message);
                    } else {
                      $bot->sendText($from,'GGAはおわったよ！');
                    }
                } else {
                  $bot->sendText($from,'GGAはたぶん10月6日です(・ω・)');
                }
                return 0;
            } else {
              // $bot->sendText($from,sprintf('%sなんですね～', $content['text']));
              // return 0;
            }

            if (stristr($content['text'], '提出') !== false) {
                if (stristr($content['text'], 'あと') !== false) {
                    $teishutu = strtotime('20160915');
                    $int = $teishutu - $today;
                    $day = ceil($int / (24 * 60 * 60));
                    $week = ceil($day / 7);
                    $format = '提出まであと%s日(%s週間)です。';
                    $message = sprintf($format, $now, $day, $week);
                    if ($day > 0) {
                      $bot->sendText($from,$message);
                    } else {
                      $bot->sendText($from,'提出日はすぎてるよ！');
                    }
                } else {
                    $bot->sendText($from,'提出は9月15日です(・ω・´)');
                }
            } else {
              $bot->sendText($from,sprintf('%sなんですね～', $content['text']));
            }
        }
    }
    return 0;
});

$app->run();
