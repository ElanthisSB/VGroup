Assignment 1:

Preferred tools:
  - N/A

Write a PHP function that accepts an integer as a parameter and outputs a numbered and sorted list of all prime numbers that are smaller than the supplied number.

Deliverable:
- Provide us with a public GitHub repository containing the assignment.

Assignment 2:

Preferred tools:
  - Composer: for dependency management
  - PHPUnit: For testing your code
  - PHP Code Sniffer: Code analyzer

Deliverable:
- Provide us with a public GitHub repository containing the assignment.

# Should give output 1 (See desired outputs below)
php assignment2.php

# Should give output 2 (See desired outputs below)
php assignment2.php --instances 2 --instance-type t2.small --allow-ssh-from 172.16.8.30

In addition, both commands for assignment #2 should render a HTML page with clear
syntax highlighting. You can earn bonus points by creating a mechanism to apply
the template using the aws cli and for monitoring and reporting the status of
the template's deployment.

Outputs:

Output #1

{
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
}


Output #2

{
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
}
