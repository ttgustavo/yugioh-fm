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

echo "Emulator process ID: $processId"

# ---- Detect heap address
heapLine=

while read -r line; do
  heapLine=`echo "$line" | grep heap`

  if [ ! -n "$heapLine" ]; then
    continue
  fi
  
  break
done < "/proc/$processId/maps"

if [ -z "$heapLine" ]; then
  "Heap not found."
  exit 1
fi

heapRange=`echo "$heapLine" | awk '{print $1}'`
heapStart=`echo "$heapRange" | cut -d'-' -f1`
heapEnd=`echo "$heapRange" | cut -d'-' -f2`

echo "Heap: $heapStart-$heapEnd"

# ---- Export heap.
# echo "Exporting heap..."
# touch /home/gustavo/Documents/YuGiOh-Heap.txt
# dd if="/proc/$processId/mem" of="/home/gustavo/Documents/YuGiOh-Heap2.txt" bs=1 skip="$((16#$heapStart))" count="$((16#$heapEnd - 16#$heapStart))" status=none
# echo "Exported."

# Current card
currentCardAddress=$((16#$heapStart + 16#d32cc4))
currentCard=`dd if="/proc/$processId/mem" bs=1 skip="$currentCardAddress" count=4 status=none | hexdump -C`

echo "$currentCard"

# 486 240 002 337 311

# From this  : 561b573e6000
# From memory: 561B573E6000