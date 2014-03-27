<!DOCTYPE html>
<html>

  <head>

    <link href='http://fonts.googleapis.com/css?family=Open+Sans:700' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>

    <title>ITMO 544 MP1</title>
    <style type="text/css">

    body {
      background-color: #b0e0e6;
    }

    h2 {
      font-family: 'Open Sans', sans-serif;
      padding: 5px 5px;
      text-align: center;
      color: orange;
      background-color: #ffefd5;
      text-shadow:
        -.05em -.05em .05em white,
        .03em .03em .05em purple;
      border: 1px solid red;
      margin-left: auto;
      margin-right: auto;
      width: 500px;
      border-radius: 5px;
    }

    .content {
      font-family: 'Lato', sans-serif;
      padding: 10px 10px;
      border: 1px solid red;
      background-color: #fffacd;
      margin-top: 20px;
      margin-left: 20px;
      margin-right: 20px;
      border-radius: 5px;
    }

    </style>
  </head>

  <body>
<?php
// Include the SDK using the Composer autoloader
require 'vendor/autoload.php';

use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
use Aws\Common\Aws;

//aws factory
$aws = Aws::factory('/var/www/vendor/aws/aws-sdk-php/src/Aws/Common/Resources/custom-config.php');
$snsclient = $aws->get('Sns'); 
$sqsclient = $aws->get('Sqs');

$topicName="mp1arsresize";

$snsresult = $snsclient->createTopic(array(
    // Name is required
    'Name' => $topicName,
));

$topicArn = $snsresult['TopicArn'];

#echo $topicArn ."\n";
#echo $phone ."\n";



$snsresult = $snsclient->setTopicAttributes(array(
    // TopicArn is required
    'TopicArn' => $topicArn,
    // AttributeName is required
    'AttributeName' => 'DisplayName',
    'AttributeValue' => 'aws544',
));

$sqsresult = $sqsclient->createQueue(array('QueueName' => 'Image_queue',));
$qurl=$sqsresult['QueueUrl'];
?>

    <h2>Picture Uploader</h2>
    <div class="content">
      <form action="process.php" method="post" enctype="multipart/form-data">
        Email: <input type="text" name="email" > <br />
        Cell Number: <input type="text" name="phone" > <br />
        Choose Image: <input type="file" name="uploaded_file" id="uploaded_file"> <br />  
        <input type="hidden" name="topicArn" value="<? echo $topicArn ?>" >
        <input type="hidden" name="qurl" value="<? echo $qurl ?>" > 
        <input type="submit"  value="submit it!" >
      </form>
    </div>
  </body>

</html>
