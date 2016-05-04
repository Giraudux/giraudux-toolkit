#!/bin/bash

delimiter="_EOF"

if [ "$#" -eq "0" ]
then
    echo "Usage : $0 [FILE]..."
else
    for file in "$@"
    do
        if [ -f "$1" ]
        then
            filename=`basename ${file}`
            echo "base64 --decode > $filename << $delimiter" > "$filename.sh"
            base64 "$file" >> "$filename.sh"
            echo "$delimiter" >> "$filename.sh"
        else
            echo "$file: file not found"
        fi
    done
fi
