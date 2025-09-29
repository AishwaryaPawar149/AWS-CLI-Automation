#!/bin/bash
set -e

BUCKET_NAME="my-bucket-$(date +%s)"
REGION="ap-south-1"

echo "Creating S3 bucket: $BUCKET_NAME"
aws s3api create-bucket --bucket $BUCKET_NAME --region $REGION --create-bucket-configuration LocationConstraint=$REGION

echo "Uploading sample file..."
echo "Hello AWS CLI Automation" > sample.txt
aws s3 cp sample.txt s3://$BUCKET_NAME/

echo "✅ S3 bucket created and file uploaded."
