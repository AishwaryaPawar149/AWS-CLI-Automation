#!/bin/bash
set -e

# Variables
INSTANCE_TYPE="t3.micro"
AMI_ID="ami-01b6d88af12965bb6"       # Amazon Linux 2023 AMI
KEY_NAME="new"                         # Existing key pair
SECURITY_GROUP_NAME="my-sg-mumbai"
REGION="ap-south-1"
SUBNET_ID="subnet-0b7ffdec16dd0024c"  # Subnet with auto-assign public IP

echo "🚀 Starting EC2 Provisioning in $REGION ..."

# 1. Check if Key Pair exists
KEY_EXISTS=$(aws ec2 describe-key-pairs --key-names $KEY_NAME --region $REGION --query 'KeyPairs[0].KeyName' --output text 2>/dev/null || echo "None")

if [ "$KEY_EXISTS" == "None" ]; then
    echo "🔑 Key pair $KEY_NAME not found. Creating..."
    aws ec2 create-key-pair \
        --key-name $KEY_NAME \
        --query 'KeyMaterial' \
        --region $REGION \
        --output text > ${KEY_NAME}.pem
    chmod 400 ${KEY_NAME}.pem
    echo "Key pair created and saved as ${KEY_NAME}.pem"
else
    echo "✅ Key pair $KEY_NAME already exists. Using it."
fi

# 2. Create Security Group if not exists
SG_ID=$(aws ec2 describe-security-groups --group-names $SECURITY_GROUP_NAME --region $REGION --query 'SecurityGroups[0].GroupId' --output text 2>/dev/null || echo "")

if [ -z "$SG_ID" ] || [ "$SG_ID" == "None" ]; then
    SG_ID=$(aws ec2 create-security-group \
        --group-name $SECURITY_GROUP_NAME \
        --description "My Security Group for EC2 in ap-south-1" \
        --region $REGION \
        --query 'GroupId' \
        --output text)
    echo "✅ Security Group Created: $SG_ID"

    # Add inbound rules
    aws ec2 authorize-security-group-ingress --group-id $SG_ID --protocol tcp --port 22 --cidr 0.0.0.0/0 --region $REGION
    aws ec2 authorize-security-group-ingress --group-id $SG_ID --protocol tcp --port 80 --cidr 0.0.0.0/0 --region $REGION
        echo "✅ Inbound rules added to Security Group."
else
    echo "✅ Security Group $SECURITY_GROUP_NAME already exists: $SG_ID"
fi

# 3. Launch EC2 Instance with auto-assign public IP
INSTANCE_ID=$(aws ec2 run-instances \
    --image-id $AMI_ID \
    --count 1 \
    --instance-type $INSTANCE_TYPE \
    --key-name $KEY_NAME \
    --security-group-ids $SG_ID \
    --subnet-id $SUBNET_ID \
    --associate-public-ip-address \
    --region $REGION \
    --query "Instances[0].InstanceId" \
    --output text)

echo "✅ EC2 Instance Created: $INSTANCE_ID"

# 4. Tag the instance
aws ec2 create-tags --resources $INSTANCE_ID --tags Key=Name,Value=MyEC2Instance-AutoIP --region $REGION
echo "🏷️ EC2 Instance Tagged Successfully."

# 5. Get Public IP
sleep 5   # wait a few seconds for IP assignment
PUBLIC_IP=$(aws ec2 describe-instances --instance-ids $INSTANCE_ID --region $REGION --query "Reservations[0].Instances[0].PublicIpAddress" --output text)
echo "🌍 Public IP of instance: $PUBLIC_IP"

read -p "✅ Script completed. Press Enter to exit......"
