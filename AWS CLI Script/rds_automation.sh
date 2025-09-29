aws rds create-db-instance \
    --db-instance-identifier mydb \
    --db-instance-class db.t3.micro \
    --engine mysql \
    --engine-version 8.0.42 \
    --master-username admin \
    --master-user-password Password123! \
    --allocated-storage 20 \
    --region ap-south-1
