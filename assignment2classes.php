<?php
namespace Assignment;

use JsonSerializable;

class ResourceProperty implements JsonSerializable {
  public function __construct() {
    $this->properties = array();
  }

  public function set( $name, $value ) {
    $this->properties[$name] = $value;
  }

  public function get( $name ) {
    return $this->properties[$name];
  }

  public function exists( $name ) {
    return array_key_exists( $name, $this->properties );
  }

  public function JsonSerialize() {
    return $this->properties;
  }
}

class Resource implements JsonSerializable {
  public function __construct( $type ) {
    $this->type = $type;
    $this->properties = new ResourceProperty();
  }

  public function setProperty( $name, $value ) {
    $this->properties->set( $name, $value );
  }

  public function JsonSerialize() {
    return [ "Properties" => $this->properties,
             "Type" => $this->type ];
  }
}

class InstanceResource extends Resource {
  public function __construct( $imageid, $instancetype ) {
    parent::__construct("AWS::EC2::Instance");

    $this->setProperty( "ImageId", $imageid );
    $this->setProperty( "InstanceType", $instancetype );
    $this->setProperty( "SecurityGroups", [[ "Ref" => "InstanceSecurityGroup" ]] );
  }
}

class SecurityGroupResource extends Resource {
  public function __construct() {
    parent::__construct("AWS::EC2::SecurityGroup");
  }

  public function addDescription( $description ) {
    $this->setProperty( "GroupDescription", $description );
  }

  public function addIngress( $cidrip, $fromport, $ipprotocol, $toport ) {
    $key = [];
    if ( $this->properties->exists( "SecurityGroupIngress" ) ) {
      $key = $this->properties->get( "SecurityGroupIngress" );
    }

    $key[] = [ "CidrIp" => $cidrip,
                "FromPort" => $fromport,
                "IpProtocol" => $ipprotocol,
                "ToPort" => $toport ];

    $this->setProperty( "SecurityGroupIngress", $key );
  }
}

class AWSTemplateGenerator {
  public function __construct() {
    // This could be created using classes, but given this excersize
    // it isn't necessary.
    $this->AWSTemplateFormatVersion = "2010-09-09";
    $this->Outputs = [
              "PublicIP" => [
                "Description" => "Public IP address of the newly created EC2 instance",
                "Value" => [
                  "Fn::GetAtt" => [ "EC2Instance", "PublicIp" ]
                ]
              ]
            ];
    $this->Resources = array();
  }

  public function addInstance( $name, $imageid, $instancetype ) {
    $this->Resources[ $name ] = new InstanceResource( $imageid, $instancetype );
  }

  public function addSecurityGroup( $name, $cidrip, $fromport, $ipprotocol, $toport, $description ) {
    $group = new SecurityGroupResource();
    $group->addDescription( $description );
    $group->addIngress( $cidrip, $fromport, $ipprotocol, $toport );

    $this->Resources[ $name ] = $group;
  }
}
?>
