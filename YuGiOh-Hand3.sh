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
