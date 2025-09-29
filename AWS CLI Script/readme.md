# 🚀 AWS Cloud Automation Project

This project demonstrates how to **automatically provision and configure AWS resources** using **AWS CLI**, along with a **PHP + Nginx web application** that uploads images directly into **Amazon S3** and stores metadata in **Amazon RDS (MySQL)**.

---

## 🔥 Features
- ✅ **EC2 Instance Automation** (Key Pairs, Security Groups, Launch)  
- ✅ **RDS MySQL Automation** (Database Instance Creation)  
- ✅ **S3 Bucket Automation** (Bucket Creation + File Upload)  
- ✅ **CloudWatch Alarm** for EC2 CPU Utilization  
- ✅ **Nginx + PHP Application** for Image Upload  
- ✅ **Integration with S3 + RDS**  
- ✅ **Presigned S3 URLs** (secure temporary image access)  
- ✅ **Attractive UI** (HTML + CSS + PHP)

---

## 🏗️ Architecture Diagram

The project follows a **3-tier architecture**:

![AWS Flow Diagram](./diagram.png)

---

## ⚙️ Workflow

1. **Automation Scripts**
   - `ec2_provision.sh` → Creates EC2, Security Group, Key Pair, assigns Public IP  
   - `rds_automation.sh` → Creates MySQL RDS instance  
   - `s3_automation.sh` → Creates S3 bucket & uploads a sample file  
   - `cloudwatch_alarm.sh` → Sets up CPU utilization alarms  

2. **Web Application**
   - Runs on EC2 with **Nginx + PHP**  
   - Upload form takes **title, description & image**  
   - Image → uploaded to **S3 bucket**  
   - Presigned URL generated (20 min expiry)  
   - Metadata stored in **RDS MySQL**  
   - Images also stored locally in `/usr/share/nginx/html/uploads/`  

---

## 💻 Tech Stack
- **AWS CLI** (Automation)  
- **EC2** (Web Hosting)  
- **RDS (MySQL)** (Database)  
- **S3** (Image Storage)  
- **CloudWatch** (Monitoring)  
- **Nginx + PHP** (Web App)  

---

## 📂 Project Structure
