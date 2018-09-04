#!/usr/bin/env python
#
# coho-ebs-snapshot-manager.py - daily snapshot management script
#
# Create new snapshots of the coho filesystems and delete old snapshots.
# This script is executed daily.
#
import boto3
import datetime
import time
    
def lambda_handler(event, context):
    INSTANCE_ID='i-0b027e229ca184e86'
    
    client = boto3.client('ec2', region_name = 'us-west-2')
    ec2 = boto3.resource('ec2')
    
    #
    # Create today's snapshots
    #
    
    # Stop the instance.  This ensures that the EBS volumes are in a consistent state and
    # we will get a good snapshot.
    print "Stopping instance %s" % INSTANCE_ID
    client.stop_instances(
        InstanceIds = [
            INSTANCE_ID,
        ]
    )
    
    # It takes a few seconds for the instance to stop.  It needs to actually be stopped
    # before we take the snapshots.
    
    while True:
      time.sleep(10)
      state = client.describe_instances(InstanceIds=[INSTANCE_ID])['Reservations'][0]['Instances'][0]['State']['Name']
      if state == 'stopped':
        break
    
    print "Stopped instance %s" % INSTANCE_ID
    
    try:
    
      # We stopped the instance.  Do this all within a try/except block. We want to
      # do our best to ensure that whatever happens (even if the backup fails) we
      # start the instance after we are done.
    
      day = datetime.datetime.today().strftime('%d')
    
      if day == 01:
          # This is the snapshot for the first day of the month.
          # Call this snapshot a 'monthly' snapshot.
          # This snapshot will be retained longer than the daily snapshot.
          print "Creating monthly snapshot"
          tagSpec = [
              {
                  'ResourceType': 'snapshot',
                  'Tags': [
                      {
                          'Key': 'Type',
                          'Value': 'Monthly'
                      },
                  ]
              },
          ]
      else:
          print "Creating daily snapshot"
          # This is the snapshot for something other than the first day of the month.
          # Call this snapshot a 'daily' snapshot.
          tagSpec = [
              {
                  'ResourceType': 'snapshot',
                  'Tags': [
                      {
                          'Key': 'Type',
                          'Value': 'Daily'
                      },
                  ]
              },
          ]
    
      YYYYMMDD = datetime.datetime.today().strftime('%Y%m%d')
    
      # Actually create the snapshots.
    
      # Snapshot the root filesystem volume.
      print "Creating rootfs snapshot"
      client.create_snapshot(
          Description = '/ %s' % (YYYYMMDD),
          VolumeId = 'vol-0ebc31751ee23c086',
          TagSpecifications = tagSpec
      )
    
      # Snapshot the volume where we store the website.
      print "Creating /var/www snapshot"
      client.create_snapshot(
          Description = '/var/www %s' % (YYYYMMDD),
          VolumeId = 'vol-07a475b8d450db3ef',
          TagSpecifications = tagSpec
      )
    
      # Snapshot the volume where we store mysql data.
      print "Creating /var/lib/mysql snapshot"
      client.create_snapshot(
          Description = '/var/lib/mysql %s' % (YYYYMMDD),
          VolumeId = 'vol-0559ccba5975f9745',
          TagSpecifications = tagSpec
      )
    
    except Exception as e:
      print(e)
    
    # The snapshots are being created now we can start the instance.  We just
    # needed the instance stopped in order to get the volumes in a consistent state
    # to start the snapshots.
    print "Starting instance %s" % INSTANCE_ID
    client.start_instances(
        InstanceIds = [
            INSTANCE_ID
        ]
    )

    return {
        "statusCode": 200
    }




    #
    # TODO - Delete old snapshots
    #
    # Keep the last 7 days of daily snapshots
    # Keep a snapshot from the first of every month for 1 year
    # Delete everything else.
    
    #
    # Find snapshots that were not taken on the first of the month that are older than 7 days.  Delete them.
    #
    #SEVEN_DAYS_AGO=`date -d '7 days ago' +%Y-%m-%d`
    #SNAPSHOTS=($(/usr/bin/aws ec2 describe-snapshots --region us-west-2 --owner-ids 660983422489 --filters Name=tag:Type,Values=Daily --query "Snapshots[?StartTime<\`$SEVEN_DAYS_AGO\`].SnapshotId" --output text))
    
    #echo "Would delete $SNAPSHOTS"
    #for SNAPSHOT in $SNAPSHOTS; do
    #  /usr/bin/aws ec2 delete-snapshot --snapshot-id $SNAPSHOT
    #done
    
    #
    # Find snapshots that are older than 1 year, delete them.
    #
    #ONE_YEAR_AGO=`date -d '1 year ago' +%Y-%m-%d`
    #SNAPSHOTS=($(/usr/bin/aws ec2 describe-snapshots --region us-west-2 --owner-ids 660983422489 --filters Name=tag:Type,Values=Monthly --query "Snapshots[?StartTime<\`$ONE_YEAR_AGO\`].SnapshotId" --output text))
    
    #echo "Would delete $SNAPSHOTS"
    #for SNAPSHOT in $SNAPSHOTS; do
    #  /usr/bin/aws ec2 delete-snapshot --snapshot-id $SNAPSHOT
    #done

if __name__ == '__main__':
    lambda_handler(None, None)
