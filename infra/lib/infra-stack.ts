import * as cdk from 'aws-cdk-lib';
import { Construct } from 'constructs';
import * as ec2 from 'aws-cdk-lib/aws-ec2';
import * as iam from 'aws-cdk-lib/aws-iam';
import * as s3 from 'aws-cdk-lib/aws-s3';

export class VisualVortex99Stack extends cdk.Stack {
  constructor(scope: Construct, id: string, props?: cdk.StackProps) {
    super(scope, id, props);
    // IAM policy
    const policyStatements: iam.PolicyStatement[] = [
	    new iam.PolicyStatement({
		    effect: iam.Effect.ALLOW,
		    actions: ['ssm:*'],
		    resources: ['*'],
	    }),
	    new iam.PolicyStatement({
		    effect: iam.Effect.ALLOW,
		    actions: ['bedrock:InvokeModel'],
		    resources: ['*'],
	    }),
    ];
    const Ec2WebServerPolicy = new iam.Policy(this, 'EC2WebServerPolicy',{
	    statements: policyStatements,
    });
    // IAM Role
    const ec2Role = new iam.Role(this, 'EC2Role', {
	    assumedBy: new iam.ServicePrincipal('ec2.amazonaws.com'),
	    managedPolicies: [
		    iam.ManagedPolicy.fromAwsManagedPolicyName('AmazonSSMManagedInstanceCore'),
	    ],
	    description: 'Role for an EC2 Instance that allows SSM and Bedrock access. Part of VisualVortex99',
    });

    ec2Role.attachInlinePolicy(Ec2WebServerPolicy);
    // VPC
    const vpc = new ec2.Vpc(this, 'VV99Vpc', {
	    maxAzs: 1,
	    natGateways: 0, // no NAT gateways please
    });

    const webServerSg = new ec2.SecurityGroup(this, 'WebServerSG', {
	    vpc,
	    description: 'Allow HTTP traffic',
	    allowAllOutbound: true,
    });
    webServerSg.addIngressRule(
	    ec2.Peer.anyIpv4(),
	    ec2.Port.tcp(80),
	    'Allow HTTP Traffic',
    );
    // EC2 Instance
    const userData = ec2.UserData.forLinux();
    userData.addCommands(
	    'yum update -y',
	    'dnf install php8.1 -y',
	    'systemctl enable --now httpd',
	    'systemctl enable --now php-fpm',
      'cd /tmp/',
      'php -r "copy(\'https://getcomposer.org/installer\', \'composer-setup.php\');"',
      'php composer-setup.php --install-dir="/usr/local/bin" --filename=composer --quiet',
      'cd /var/www/ && aws s3 sync s3://visualvortex99-webassets/ .',
      'composer update',
      'chown -R apache:apache /var/www/html/',
    );

    const webServer = new ec2.Instance(this, 'VV99WebServer',{
	    vpc,
	    instanceType: ec2.InstanceType.of(ec2.InstanceClass.T2, ec2.InstanceSize.SMALL),
	    machineImage: ec2.MachineImage.latestAmazonLinux2023(),
	    userData,
	    securityGroup: webServerSg,
	    vpcSubnets: {
		    subnets: vpc.publicSubnets, // only use public
	    },
	    role: ec2Role,
    });
    
    // S3 Bucket
    // let's try to see if the bucket exists
    let webAssetsBucket: s3.Bucket;
    try {
      webAssetsBucket = s3.Bucket.fromBucketName(this, 'existingBucket', 'visualvortex99-webassets') as s3.Bucket;
    } catch(err) {
      webAssetsBucket = new s3.Bucket(this, 'WebAssetsBucket', {
        bucketName: 'visualvortex99-webassets'
      });
    }
    webAssetsBucket.grantReadWrite(ec2Role);

  }
}
