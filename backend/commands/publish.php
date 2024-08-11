<?php

namespace app\commands;

require_once('vendor/autoload.php');

use PubNub\PubNub;
use PubNub\PNConfiguration;

$pnconf = new PNConfiguration();
$pubnub = new PubNub($pnconf);

$pnconf->setSubscribeKey("sub-c-7a080724-d4d0-46af-a644-53d651aa3dd4");
$pnconf->setPublishKey("pub-c-ed0d5f65-4368-492b-a376-0b82917208b9");
$pnconf->setUserId("629");

// Use the publish command separately from the Subscribe code shown above.
// Subscribe is not async and will block the execution until complete.
$result = $pubnub->publish()
            ->channel("p2p_order_32024_07_22_14_49_12")
            ->message("Hello admin")
            ->sync();

print_r($result);