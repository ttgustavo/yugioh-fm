#!/bin/bash

# Detect if has address input.
if [ -z "$1" ]; then
  echo "Usage: $0 [RAM address in hex]"
  exit 1
fi

# Detect emulator process ID
while read -r line; do
  processName=`echo $line | awk '{print $4}'`

  if [ ! "$processName" = "duckstation-qt" ]; then
    continue
  fi

  processId=`echo $line | awk '{print $1}'`
done < <(ps -A | grep duckstation)

if [ -z "$processId" ]; then
  echo "Emulator not opened."
  exit
fi

# Read cards
while true
do
  address=`echo "$((16#$1))"`
  offsetCurrentCard=$((16#9b338))
  offsetCard1=$((16#1a7ae4))
  offsetCard2=$((16#1a7b00))
  offsetCard3=$((16#1a7b1c))
  offsetCard4=$((16#1a7b38))
  offsetCard5=$((16#1a7b54))

  card1=`dd if="/proc/$processId/mem" bs=1 skip="$(($address + $offsetCard1))" count=2 status=none | hexdump -C -d`
  card2=`dd if="/proc/$processId/mem" bs=1 skip="$(($address + $offsetCard2))" count=2 status=none | hexdump -C -d`
  card3=`dd if="/proc/$processId/mem" bs=1 skip="$(($address + $offsetCard3))" count=2 status=none | hexdump -C -d`
  card4=`dd if="/proc/$processId/mem" bs=1 skip="$(($address + $offsetCard4))" count=2 status=none | hexdump -C -d`
  card5=`dd if="/proc/$processId/mem" bs=1 skip="$(($address + $offsetCard5))" count=2 status=none | hexdump -C -d`

  cardNumber1=`echo "$card1" | awk 'NR==2 {print $2}'`
  cardNumber2=`echo "$card2" | awk 'NR==2 {print $2}'`
  cardNumber3=`echo "$card3" | awk 'NR==2 {print $2}'`
  cardNumber4=`echo "$card4" | awk 'NR==2 {print $2}'`
  cardNumber5=`echo "$card5" | awk 'NR==2 {print $2}'`

  echo "$((10#$cardNumber1)) $((10#$cardNumber2)) $((10#$cardNumber3)) $((10#$cardNumber4)) $((10#$cardNumber5))"

  php yugioh-run.php $((10#$cardNumber1)) $((10#$cardNumber2)) $((10#$cardNumber3)) $((10#$cardNumber4)) $((10#$cardNumber5))

  sleep 3
  clear
done