#!/bin/bash

# Script Name: upload-s3.sh
#
# Description: This script is used to upload files to a cloud bucket.
#
# Prerequisites:
# - The BUCKET_URL environment variable must be set before running the script.
#   Example: export BUCKET_URL=my-bucket-name
#
# Usage: ./script.sh [option] [zip]
#   option - The type of file to upload (grid, signalement, or image)
#   zip - The code of the department (for signalement)
#
# Example 1: ./scripts/upload-s3.sh signalement 33
# Example 2: ./scripts/upload-s3.sh entreprisespubliques
# Example 3: ./scripts/upload-s3.sh entreprisespubliques-detectioncanine
#
# Notice:
# - File must be executed in local, ignored during the deployment
#

if [ -z "$BUCKET_URL" ]; then
  echo "BUCKET_URL variable not set"
else
  echo "The value of BUCKET_URL is: $BUCKET_URL"
  option=$1
  uuid=$2
  debug=${3:null}
  if [ -z "$option" ]; then
    echo "option argument is missing: ./scripts/upload-s3.sh [option]"
    exit 1
  fi
  case "$option" in
    "signalement")
      if [ -z "$uuid" ]; then
        echo "uuid argument is missing: ./scripts/upload-s3.sh [option] [uuid]"
        exit 1
      fi
      echo "Upload signalements_$2.csv to cloud..."
      aws s3 cp data/signalement/signalements_${uuid}.csv s3://${BUCKET_URL}/csv/ ${debug}
      aws s3 ls s3://${BUCKET_URL}/csv/signalements_${uuid}.csv
      ;;
    "entreprisespubliques")
      echo "Upload entreprises.csv to cloud..."
      aws s3 cp data/entreprises.csv s3://${BUCKET_URL}/csv/ ${debug}
      aws s3 ls s3://${BUCKET_URL}/csv/entreprises.csv
      ;;
    "entreprisespubliques-detectioncanine")
      echo "Upload entreprises-detection-canine.csv to cloud..."
      aws s3 cp data/entreprises-detection-canine.csv s3://${BUCKET_URL}/csv/ ${debug}
      aws s3 ls s3://${BUCKET_URL}/csv/entreprises-detection-canine.csv
      ;;
    "entreprises-utilisateurs")
      echo "Upload entreprises-utilisateurs.csv to cloud..."
      aws s3 cp data/entreprises-utilisateurs.csv s3://${BUCKET_URL}/csv/ ${debug}
      aws s3 ls s3://${BUCKET_URL}/csv/entreprises-utilisateurs.csv
      ;;
    *)
      echo "Invalid argument. Please use 'signalement' or 'entreprisespubliques' or 'entreprisespubliques-detectioncanine'"
      ;;
  esac
fi
