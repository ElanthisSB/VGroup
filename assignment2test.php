<?php
namespace Assignment;
require "assignment2classes.php";

use PHPUnit\Framework\TestCase;

class AWSTemplateGeneratorTest extends TestCase {
  public function testResourceProperty() {
    $prop = new ResourceProperty();

    $this->assertJsonStringEqualsJsonString( "[]", json_encode( $prop ), "Test without keys" );

      $this->assertFalse( $prop->exists( "ImageId" ), "ImageId should not exist." );
      $prop->set( "ImageId", "ami-b97a12ce" );
      $this->assertJsonStringEqualsJsonString( '{"ImageId": "ami-b97a12ce"}', json_encode( $prop ), "Test with 1 key" );
      $this->assertTrue( $prop->exists( "ImageId" ), "ImageId should exist." );

      $this->assertEquals( "ami-b97a12ce", $prop->get( "ImageId" ), "ImageId should be what we set it to." );

      $prop->set( "InstanceType", "t2.micro" );
      $prop->set( "SecurityGroups", [ (object) [ "Ref" => "InstanceSecurityGroup" ] ] );
      $this->assertJsonStringEqualsJsonString( '{
        "ImageId": "ami-b97a12ce",
        "InstanceType": "t2.micro",
        "SecurityGroups": [
          {
            "Ref": "InstanceSecurityGroup"
          }
        ]
      }', json_encode( $prop ), "Test with 1 key" );
  }

  public function testResource() {
    $prop = new Resource( "AWS::EC2::Instance" );

    $this->assertJsonStringEqualsJsonString( '{
        "Properties": [],
        "Type": "AWS::EC2::Instance"
      }', json_encode( $prop ), "Test without properties" );

    $prop->setProperty( "ImageId", "ami-b97a12ce" );
    $prop->setProperty( "InstanceType", "t2.micro" );
    $this->assertJsonStringEqualsJsonString( '{
        "Properties": {"ImageId": "ami-b97a12ce","InstanceType": "t2.micro"},
        "Type": "AWS::EC2::Instance"
      }', json_encode( $prop ), "Test with simple properties" );

    $prop->setProperty( "SecurityGroups", [[ "Ref" => "InstanceSecurityGroup" ]] );
    $this->assertJsonStringEqualsJsonString( '{
        "Properties": {
          "ImageId": "ami-b97a12ce",
          "InstanceType": "t2.micro",
          "SecurityGroups": [{"Ref": "InstanceSecurityGroup"}]
          },
        "Type": "AWS::EC2::Instance"
      }', json_encode( $prop ), "Test with complex properties" );
  }

  public function testArrayOfResources() {
    $resources = array();

    $res = new Resource( "AWS::EC2::Instance" );
    $res->setProperty( "ImageId", "ami-b97a12ce" );
    $res->setProperty( "InstanceType", "t2.small" );
    $res->setProperty( "SecurityGroups", [[ "Ref" => "InstanceSecurityGroup" ]] );

    $resources[ "EC2Instance" ] = $res;
    $this->assertJsonStringEqualsJsonString( '{
        "EC2Instance": {
          "Properties": {
            "ImageId": "ami-b97a12ce",
            "InstanceType": "t2.small",
            "SecurityGroups": [
              {
                "Ref": "InstanceSecurityGroup"
              }
            ]
          },
          "Type": "AWS::EC2::Instance"
        }
      }', json_encode( $resources ), "Test with one resource" );

    $resources[ "EC2Instance2" ] = new InstanceResource( "ami-b97a12ce", "t2.small" );
    $this->assertJsonStringEqualsJsonString( '{
      "EC2Instance": {
        "Properties": {
          "ImageId": "ami-b97a12ce",
          "InstanceType": "t2.small",
          "SecurityGroups": [
            {
              "Ref": "InstanceSecurityGroup"
            }
          ]
        },
        "Type": "AWS::EC2::Instance"
      },
      "EC2Instance2": {
        "Properties": {
          "ImageId": "ami-b97a12ce",
          "InstanceType": "t2.small",
          "SecurityGroups": [
            {
              "Ref": "InstanceSecurityGroup"
            }
          ]
        },
        "Type": "AWS::EC2::Instance"
      } }', json_encode( $resources ), "Test with two of the same resources" );

    $res = new SecurityGroupResource();
    $res->addDescription( "Enable SSH access via port 22" );
    $res->addIngress( "172.16.8.30/32", "22", "tcp", "22" );

    $resources[ "InstanceSecurityGroup" ] = $res;

    $this->assertJsonStringEqualsJsonString( '{
      "EC2Instance": {
        "Properties": {
          "ImageId": "ami-b97a12ce",
          "InstanceType": "t2.small",
          "SecurityGroups": [
            {
              "Ref": "InstanceSecurityGroup"
            }
          ]
        },
        "Type": "AWS::EC2::Instance"
      },
      "EC2Instance2": {
        "Properties": {
          "ImageId": "ami-b97a12ce",
          "InstanceType": "t2.small",
          "SecurityGroups": [
            {
              "Ref": "InstanceSecurityGroup"
            }
          ]
        },
        "Type": "AWS::EC2::Instance"
      },
      "InstanceSecurityGroup": {
        "Properties": {
          "GroupDescription": "Enable SSH access via port 22",
          "SecurityGroupIngress": [
            {
              "CidrIp": "172.16.8.30/32",
              "FromPort": "22",
              "IpProtocol": "tcp",
              "ToPort": "22"
            }
          ]
        },
        "Type": "AWS::EC2::SecurityGroup"
      }
    }', json_encode( $resources ), "Test with multiple types of resources" );
  }

  public function testTemplateGenerator() {
    $template = new AWSTemplateGenerator();

    $this->assertJsonStringEqualsJsonString( '{
      "AWSTemplateFormatVersion": "2010-09-09",
      "Outputs": {
        "PublicIP": {
          "Description": "Public IP address of the newly created EC2 instance",
          "Value": {
            "Fn::GetAtt": [
              "EC2Instance",
              "PublicIp"
            ]
          }
        }
      },
    "Resources": [] }', json_encode( $template ), "Simple test" );

    $template->addInstance( "EC2Instance", "ami-b97a12ce", "t2.small" );
    $this->assertJsonStringEqualsJsonString( '{
      "AWSTemplateFormatVersion": "2010-09-09",
      "Outputs": {
        "PublicIP": {
          "Description": "Public IP address of the newly created EC2 instance",
          "Value": {
            "Fn::GetAtt": [
              "EC2Instance",
              "PublicIp"
            ]
          }
        }
      },
    "Resources": {
      "EC2Instance": {
        "Properties": {
          "ImageId": "ami-b97a12ce",
          "InstanceType": "t2.small",
          "SecurityGroups": [
            {
              "Ref": "InstanceSecurityGroup"
            }
          ]
        },
        "Type": "AWS::EC2::Instance"
      } } }', json_encode( $template ), "With a resource" );

    $template->addSecurityGroup( "InstanceSecurityGroup", "172.16.8.30/32", "22", "tcp", "22", "Enable SSH access via port 22" );
    $this->assertJsonStringEqualsJsonString( '{
      "AWSTemplateFormatVersion": "2010-09-09",
      "Outputs": {
        "PublicIP": {
          "Description": "Public IP address of the newly created EC2 instance",
          "Value": {
            "Fn::GetAtt": [
              "EC2Instance",
              "PublicIp"
            ]
          }
        }
      },
    "Resources": {
      "EC2Instance": {
        "Properties": {
          "ImageId": "ami-b97a12ce",
          "InstanceType": "t2.small",
          "SecurityGroups": [
            {
              "Ref": "InstanceSecurityGroup"
            }
          ]
        },
        "Type": "AWS::EC2::Instance"
      },
      "InstanceSecurityGroup": {
        "Properties": {
          "GroupDescription": "Enable SSH access via port 22",
          "SecurityGroupIngress": [
            {
              "CidrIp": "172.16.8.30/32",
              "FromPort": "22",
              "IpProtocol": "tcp",
              "ToPort": "22"
            }
          ]
        },
        "Type": "AWS::EC2::SecurityGroup"
      } } }', json_encode( $template ), "With a resource and security group" );
  }

  public function testOutput1() {
    $template = new AWSTemplateGenerator();
    $template->addInstance( "EC2Instance", "ami-b97a12ce", "t2.micro" );
    $template->addSecurityGroup( "InstanceSecurityGroup", "0.0.0.0/0", "22", "tcp", "22", "Enable SSH access via port 22" );

    $this->assertJsonStringEqualsJsonString( '{
      "AWSTemplateFormatVersion": "2010-09-09",
      "Outputs": {
        "PublicIP": {
          "Description": "Public IP address of the newly created EC2 instance",
          "Value": {
            "Fn::GetAtt": [
              "EC2Instance",
              "PublicIp"
            ]
          }
        }
      },
      "Resources": {
        "EC2Instance": {
          "Properties": {
            "ImageId": "ami-b97a12ce",
            "InstanceType": "t2.micro",
            "SecurityGroups": [
              {
                "Ref": "InstanceSecurityGroup"
              }
            ]
          },
          "Type": "AWS::EC2::Instance"
        },
        "InstanceSecurityGroup": {
          "Properties": {
            "GroupDescription": "Enable SSH access via port 22",
            "SecurityGroupIngress": [
              {
                "CidrIp": "0.0.0.0/0",
                "FromPort": "22",
                "IpProtocol": "tcp",
                "ToPort": "22"
              }
            ]
          },
          "Type": "AWS::EC2::SecurityGroup"
        }
      }
    }', json_encode( $template ), "Test of output 1" );
  }

  public function testOutput2() {
    $template = new AWSTemplateGenerator();

    for( $i = 1; $i <= 2; $i++ ) {
      $template->addInstance( "EC2Instance" . ($i>1?$i:""), "ami-b97a12ce", "t2.small" );
    }

    $template->addSecurityGroup( "InstanceSecurityGroup", "172.16.8.30/32", "22", "tcp", "22", "Enable SSH access via port 22" );
    $this->assertJsonStringEqualsJsonString( '{
      "AWSTemplateFormatVersion": "2010-09-09",
      "Outputs": {
        "PublicIP": {
          "Description": "Public IP address of the newly created EC2 instance",
          "Value": {
            "Fn::GetAtt": [
              "EC2Instance",
              "PublicIp"
            ]
          }
        }
      },
      "Resources": {
        "EC2Instance": {
          "Properties": {
            "ImageId": "ami-b97a12ce",
            "InstanceType": "t2.small",
            "SecurityGroups": [
              {
                "Ref": "InstanceSecurityGroup"
              }
            ]
          },
          "Type": "AWS::EC2::Instance"
        },
        "EC2Instance2": {
          "Properties": {
            "ImageId": "ami-b97a12ce",
            "InstanceType": "t2.small",
            "SecurityGroups": [
              {
                "Ref": "InstanceSecurityGroup"
              }
            ]
          },
          "Type": "AWS::EC2::Instance"
        },
        "InstanceSecurityGroup": {
          "Properties": {
            "GroupDescription": "Enable SSH access via port 22",
            "SecurityGroupIngress": [
              {
                "CidrIp": "172.16.8.30/32",
                "FromPort": "22",
                "IpProtocol": "tcp",
                "ToPort": "22"
              }
            ]
          },
          "Type": "AWS::EC2::SecurityGroup"
        }
      }
    }', json_encode( $template ), "Test of output 2" );
  }
}
?>
