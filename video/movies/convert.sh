#!/bin/bash

for file in $(ls Family*)
do
	ffmpeg -y -i $file -preset ultrafast -threads 4 -vf scale=640:-1 $(basename $file).webm
done
