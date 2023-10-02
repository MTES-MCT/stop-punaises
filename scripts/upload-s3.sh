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
#   zip - The code of the department
#
# Example 1: ./scripts/upload-s3.sh grid 33
# Example 2: ./scripts/upload-s3.sh signalement 33
# Example 3: ./scripts/upload-s3.sh image 33
# Example 4: ./scripts/upload-s3.sh mapping-doc 33
# Example 5: ./scripts/upload-s3.sh process-all 33
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
  if [ -z "$uuid" ]; then
    echo "uuid argument is missing: ./scripts/upload-s3.sh [option] [uuid]"
    exit 1
  fi
  case "$option" in
    "signalement")
      echo "Upload signalements_$2.csv to cloud..."
      aws s3 cp data/signalement/signalements_${uuid}.csv s3://${BUCKET_URL}/csv/ ${debug}
      aws s3 ls s3://${BUCKET_URL}/csv/signalements_${uuid}.csv
      ;;
    "entreprisespubliques")
      echo "Upload entreprises.csv to cloud..."
      aws s3 cp data/entreprises.csv s3://${BUCKET_URL}/csv/ ${debug}
      aws s3 ls s3://${BUCKET_URL}/csv/entreprises.csv
      ;;
    *)
      echo "Invalid argument. Please use 'grid' or 'signalement' or 'image' or 'mapping-doc' or 'process-all'"
      ;;
  esac
fi
