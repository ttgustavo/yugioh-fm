#!/bin/bash

# List all process created by user.
processId=

# ---- Detect emulator process ID
while read -r line; do
  processName=$(echo $line | awk '{print $4}')
  
  if [ ! "$processName" = "duckstation-qt" ]; then
    continue
  fi

  processId=`echo $line | awk '{print $1}'`  # Plus 0 to convert to integer
done < <(ps -A | grep duckstation)

if [ -z "$processId" ]; then
  echo "Emulator not opened."
  exit 1
fi

# ---- Reading memory
if [ ! -d "/proc/$processId" ]; then
  echo "Emulator process not found."
  exit 1
fi

addressNumber=$((16#7f1c72c00000 + 16#9b338))
currentCard=`dd if="/proc/$processId/mem" bs=1 skip="$addressNumber" count=2 status=none | hexdump -d`

echo "$currentCard" | awk 'NR==1 {print $2}'

declare -i row=0

while read -r line; do
  echo "$line"

  lineWithDelete=`echo "$line" | grep deleted`
  if [ ! -n "$lineWithDelete" ]; then
    continue
  fi

  memoryRange=`echo "$line" | awk '{print $1}'`
  memoryStart=`echo "$memoryRange" | cut -d'-' -f1`
  memoryEnd=`echo "$memoryRange" | cut -d'-' -f2`

  if [ "$row" -eq 0 ]; then
    addressNumber="$((16#$memoryStart + 16#0009b338))"
    # dd if="/proc/$processId/mem" bs=1 skip="$addressNumber" count=2 status=none | hexdump -e '16/1 "%02x " "\n"' -d
    currentCard=`dd if="/proc/$processId/mem" bs=1 skip="$addressNumber" count=2 status=none | hexdump -d`
    echo "$currentCard" | awk 'NR==1 {print $2}'
    # break
  fi

  row+=1
done < "/proc/$processId/maps"

# while read -r line; do
#   echo "$line"
# done < "/proc/${processId}/maps"
#endregion