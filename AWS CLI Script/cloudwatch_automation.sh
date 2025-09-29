#!/bin/bash
set -e

INSTANCE_ID="i-0b3a72adf219d9b65"
ALARM_NAME="CPU_Alarm_$INSTANCE_ID"
REGION="ap-south-1"

aws cloudwatch put-metric-alarm \
    --alarm-name $ALARM_NAME \
    --metric-name CPUUtilization \
    --namespace AWS/EC2 \
    --statistic Average \
    --period 300 \
    --threshold 70 \
    --comparison-operator GreaterThanThreshold \
    --dimensions Name=InstanceId,Value=$INSTANCE_ID \
    --evaluation-periods 1 \
    --alarm-actions "arn:aws:sns:ap-south-1:141729833326:MySNSTopic" \
    --region $REGION

echo "✅ CloudWatch CPU alarm created for instance $INSTANCE_ID."
