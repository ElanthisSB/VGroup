<?php
namespace Assignment;
require "assignment2classes.php";

function isCLI() {
  return ( php_sapi_name() === 'cli' );
}

$args = [];
if ( isCLI() ) {
  $args = getopt( "", ["instances:", "instance-type:", "allow-ssh-from:"] );
  var_dump( $args );
  $instances = 1;
  if ( array_key_exists( "instances", $args ) ) {
    $instances = $args[ "instances" ];
  }

  $instance_type = "t2.micro";
  if ( array_key_exists( "instance-type", $args ) ) {
    $instance_type = $args[ "instance-type" ];
  }

  $ssh = "0.0.0.0/0";
  if ( array_key_exists( "allow-ssh-from", $args ) ) {
    $ssh = $args[ "allow-ssh-from" ] . "/32";
  }

  $template = new AWSTemplateGenerator();

  for( $i = 1; $i <= $instances; $i++ ) {
    $template->addInstance( "EC2Instance" . ($i>1?$i:""), "ami-b97a12ce", $instance_type );
  }

  $template->addSecurityGroup( "InstanceSecurityGroup", $ssh, "22", "tcp", "22", "Enable SSH access via port 22" );

  echo json_encode( $template, JSON_PRETTY_PRINT );
}

?>
