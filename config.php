<?php  // Moodle configuration file

require 'lib/aws.phar';
use Aws\SecretsManager\SecretsManagerClient;
use Aws\Exception\AwsException;
use Aws\Iam\IamClient;

unset($CFG);
global $CFG;

$client = new SecretsManagerClient([
   'version' => '2017-10-17',
   'region' => 'us-east-1',
]);

$secretName = 'arn:aws:secretsmanager:us-east-1:932315984983:secret:moodle-MariaDB-7KFMoy';

try {
   $result = $client->getSecretValue([
      'SecretId' => $secretName,
   ]);
} catch (AwsException $e) {
    $error = $e->getAwsErrorCode();
}

// Decrypts secret using the associated KMS CMK.
// Depending on whether the secret is a string or binary, one of these fields will be populated.
if (isset($result['SecretString'])) {
    $secret = $result['SecretString'];
} 

$CFG = new stdClass();

$CFG->dbtype    = 'mariadb';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'moodle-database.cgumh3sxn9s8.us-east-1.rds.amazonaws.com';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = json_decode($secret)->{'username'};
$CFG->dbpass    = json_decode($secret)->{'password'};
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => 3306,
  'dbsocket' => '',
  'dbcollation' => 'utf8mb4_unicode_ci',
);

$CFG->wwwroot   = 'http://moodle-iga-1503938063.us-east-1.elb.amazonaws.com';
$CFG->dataroot  = '/var/www/moodle/data';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(__DIR__ . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
