<?php

namespace app\commands;

require_once('vendor/autoload.php');

use PubNub\PubNub;
use PubNub\Enums\PNStatusCategory;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\PNConfiguration;

$pnconf = new PNConfiguration();
$pubnub = new PubNub($pnconf);

$pnconf->setSubscribeKey("sub-c-7a080724-d4d0-46af-a644-53d651aa3dd4");
$pnconf->setPublishKey("pub-c-ed0d5f65-4368-492b-a376-0b82917208b9");
$pnconf->setUserId("638");

class MySubscribeCallback extends SubscribeCallback {
    function status($pubnub, $status) {
        if ($status->getCategory() === PNStatusCategory::PNUnexpectedDisconnectCategory) {
            // This event happens when radio / connectivity is lost
        } else if ($status->getCategory() === PNStatusCategory::PNConnectedCategory) {
            // Connect event. You can do stuff like publish, and know you'll get it
            // Or just use the connected event to confirm you are subscribed for
            // UI / internal notifications, etc
        } else if ($status->getCategory() === PNStatusCategory::PNDecryptionErrorCategory) {
            // Handle message decryption error. Probably client configured to
            // encrypt messages and on live data feed it received plain text.
        }
    } 

    function message($pubnub, $message) {
        // Handle new message stored in message.message
        print_r($message->getMessage() . PHP_EOL );
        print_r($message->getPublisher() . PHP_EOL);
    }

    function presence($pubnub, $presence) {
        // handle incoming presence data
    }
}

$subscribeCallback = new MySubscribeCallback();
$pubnub->addListener($subscribeCallback);

// Subscribe to a channel, this is not async.
$pubnub->subscribe()
    ->channels("p2p_order_32024_07_22_14_49_12")
    ->execute();