<!DOCTYPE html>
<?php
#retrieve these values that were set in process.php to make our code more flexible
session_start();
sleep(1);
$bucket = $_SESSION['bucket'];
$rawurl = $_SESSION['rawurl'];
$finishedurl = $_SESSION['finishedurl'];
$newfilename = $_SESSION['newkey'];
$queueURL = $_SESSION['queueurl'];
$receipthandle = $_SESSION['receipthandle'];
$topicArn = $_SESSION['topicarn'];

// Include the SDK using the Composer autoloader
require 'vendor/autoload.php';

use Aws\Sns\SnsClient;
use Aws\SimpleDb\SimpleDbClient; 
use Aws\S3\S3Client;
use Aws\Sqs\SqsClient;
use Aws\Common\Aws;
use Aws\SimpleDb\Exception\InvalidQueryExpressionException;

//aws factory
$aws = Aws::factory('/var/www/vendor/aws/aws-sdk-php/src/Aws/Common/Resources/custom-config.php');

// Instantiate the S3 client with your AWS credentials and desired AWS region
$client = $aws->get('S3');

$sdbclient = $aws->get('SimpleDb');

$sqsclient = $aws->get('Sqs');

$snsclient = $aws->get('Sns'); 


############################################################################
# Set object expire to remove the image in 10 minutes
#############################################################################
$actualtime = time();
$expirationminutes = 1;
$expirationtime = (60 * $expirationminutes) + $actualtime;
$expirationdate = date("G:i:s", $expirationtime);

//echo $expirationtime;
//echo $expirationdate;

$result = $client->putBucketLifecycle(array(
    'Bucket' => $bucket,
    'Rules' => array(
        array(
            'Expiration' => array(
                #'Date' => 'mixed type: string (date format)|int (unix timestamp)|\DateTime',
                'Days' => 1,
            ),
            'Prefix' => '',
            'Status' => 'Enabled',
        ),
        // ... repeated
    ),
));


############################################################################
# Set ACL to public
#############################################################################
$result = $client -> putObjectAcl(array(
	'ACL' => 'public-read',
	'Bucket' => $bucket,
	'Key' => $newfilename,
));


############################################################################
# Consume the queue
#############################################################################
$result = $sqsclient->deleteMessage(array(
    'QueueUrl' => $queueURL,
    'ReceiptHandle' => $receipthandle,
));


############################################################################
# Send the SMS of the finished S3 URL
#############################################################################
$result = $snsclient->publish(array(
    'TopicArn' => $topicArn,
    'TargetArn' => $topicArn,
    // Message is required
    'Message' => 'Thank you for uploading the image!',
    'Subject' => $finishedurl,
    'MessageStructure' => 'sms',
));


############################################################################
# Destroy the session
#############################################################################
unset($_SESSION);
$_SESSION=array();
session_destroy();
?> 


<html>
<head>
<title>Cleanup</title>
</head>

<body>
Here you can see the old and new image!
<table width="800px">
  <tr>
    <td><img src="<? echo $rawurl ?>" /></td>
    <td><img src="<? echo $finishedurl ?>" /></td>
  </tr>
</table>

</body>
</html>
