#!/bin/bash -

# Copyright (C) 2008 Sebastian Kemper (sebastian_ml @ gmx net)

# This program is free software: you can redistribute it and/or modify it under
# the terms of the GNU General Public License version 2 as published by the
# Free Software Foundation.

# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
# details.

# You should have received a copy of the GNU General Public License along with
# this program.  If not, see <http://www.gnu.org/licenses/>.

# This script converts video files in batch mode to MP4 (H264 or XviD/AAC). An
# initial configuration file (~/.avi2mp4.conf) will be created.

# Note: Before putting this script to serious use encode a few samples.

MinimumVersion=2
E_BAD_VERSION=71
if [ "$BASH_VERSION" \< "$MinimumVersion" ]; then
  echo This script works only with Bash, version $MinimumVersion or greater.
  echo Upgrade strongly recommended.
  exit $E_BAD_VERSION
fi

# Set a sane/secure path
PATH='/usr/local/bin:/bin:/usr/bin'
\export PATH

# Clear all aliases (leading "\" inhibits alias expansion)
\unalias -a

# Clear the command path hash
hash -r

# Set the hard limit to 0 to turn off core dumps
ulimit -H -c 0 --

# Set a sane/secure IFS
IFS=$' \t\n'

# Set a sane/secure umask
umask 022

unset -v LC_ALL

VERSION=1.1.19
AVI2MP4=$(basename "$0")

START_DIR="$PWD"

CMD_ABORT="Command terminated with non-zero exit status."

LC_NUMERIC=POSIX

export LC_NUMERIC

# These variables get unset for every new input file
all_media_vars=(CH CW HAS_TTXT ID_AUDIO_CODEC ID_AUDIO_NCH ID_DEMUXER ID_LENGTH
                ID_VIDEO_ASPECT ID_VIDEO_FPS ID_VIDEO_HEIGHT ID_VIDEO_WIDTH
                MENCODER_OPTS2 VFOPTS SUBS_SRT SUBS_VOBSUB SUBS_SUB SUBS_TTXT)

# Variables that are filled with the output of "mplayer -identify"
mplayer_media_vars=(ID_AUDIO_CODEC ID_AUDIO_NCH ID_DEMUXER ID_LENGTH
                    ID_VIDEO_ASPECT ID_VIDEO_FPS ID_VIDEO_HEIGHT
                    ID_VIDEO_WIDTH)

# Variables that get set when reading the configuration file
conf_vars=(CT_PERCENT CT_QUAL CT_X264_CRF FAAC_ENCOPTS MAX_BITRATE MAX_RES
           MENCODER_OPTS MP4BOX_IPOD NAAC_ENCOPTS NERO_OVERRIDE RENICE TMP_DIR
           USE_NERO_AAC VIDEO_CODEC WORK_DIR X264_ENCOPTS XVID_ENCOPTS
           SUBTITLES TRACK_LANG SBTL)

essential_commands=(bc xargs mplayer mencoder aacgain MP4Box sed grep stat cat
                    basename dirname mv rm cp)

echo -e "\n\033[1mavi2mp4.sh v$VERSION\033[0m\n"

E_CMD=66          # Command terminated with non-zero exit status
E_WRTBL=67        # File or directory not writable
E_RDBL=68         # File or directory not readable
E_CFG=69          # Error in configuration
E_MISSING_CMD=70  # Missing command
# E_BAD_VERSION=71 defined at the top

if [ -z "$PWD" ]; then
  echo "\$PWD is unset." >&2
  exit $E_CFG
fi

if [ ! -d "$PWD" ]; then
  echo "\$PWD is not a directory." >&2
  exit $E_CFG
fi

function e {
  if [ "$ERROR" != 0 ]; then
    echo -e "\a\nAn error occured.\n\n$1\n" >&2
    exit $2
  fi
}

function warn {
  echo -e "\a\n$1\n" >&2
}

function clear_variables {
  unset -v ${all_media_vars[@]}
}

# Identify media properties of a file 
function myidentify {
  mplayer -endpos .1 -vo null -ao null -frames 1 -identify "$1" 2> /dev/null \
                                                                  | grep '^ID_'
}

function rm_log_and_lock {
  rm --force "$TMP_DIR/${RND_ASCII}_myidentify.log" "$TMP_DIR/$BASENAME.lock"
}

# Read the first line and clean it up
function clean_line {
  head --lines=1 "$1" | grep --extended-regexp '^[-a-zA-Z0-9_.,:=/" ]+$' | \
                            sed --regexp-extended 's/"//g;s/^[ ]*//;s/[ ]*$//'
}

# Search for variable $1
function get_var {
  grep --extended-regexp '^'$1'=[-a-zA-Z0-9_.,:=/" ]+$' "$2" | tail --lines=1 \
                | sed --regexp-extended 's/^'$1'=//;s/"//g;s/^[ ]*//;s/[ ]*$//'
}

if [ -z "$HOME" ]; then
  ERROR=1
  e "\$HOME unset or empty." $E_CFG
fi

if [ ! -e "$HOME/.avi2mp4.conf" ]; then
  echo -en "Creating $HOME/.avi2mp4.conf ... "
  cat << EOF > "$HOME/.avi2mp4.conf"
TMP_DIR=$HOME
WORK_DIR=$HOME
MAX_BITRATE=800
# Works for PSPs:
X264_ENCOPTS=subq=5:frameref=3:turbo=1:mixed_refs=1:cabac=1:level_idc=30:chroma\
_me=1:me=hex:threads=auto:bframes=2:trellis=1:b_pyramid=none:8x8dct=0
# Works for PSPs and iPods:
X264_ENCOPTS=subq=5:frameref=3:turbo=1:mixed_refs=1:cabac=0:level_idc=21:chroma\
_me=1:me=hex:threads=auto:bframes=0:8x8dct=0
# Works for PSPs (although resolution seems to be limited to 320x240) and iPods:
XVID_ENCOPTS=autoaspect=1:max_bframes=0:container_frame_overhead=0:chroma_opt=1\
:lumi_mask=0
#USE_NERO_AAC=yes
FAAC_ENCOPTS=-q 90
NAAC_ENCOPTS=-q 0.39 -lc
#NERO_OVERRIDE=wine neroAacEnc_SSE.exe
MENCODER_OPTS=-sws 1
MAX_RES=320x240
#MP4BOX_IPOD=yes
#RENICE=19
VIDEO_CODEC=xvid
#VIDEO_CODEC=x264
CT_PERCENT=5
CT_QUAL=55
CT_X264_CRF=18
SUBTITLES=no
TRACK_LANG=eng
SBTL=no
EOF
  ERROR=$?
  e "\nUnable to create $HOME/.avi2mp4.conf." $E_WRTBL
  echo done.
  echo
fi

if [ ! -r "$HOME/.avi2mp4.conf" ]; then
  ERROR=1
  e "Read access to $HOME/.avi2mp4.conf denied." $E_RDBL
fi

unset -v ${conf_vars[@]}

for i in ${conf_vars[@]}; do
  declare $i="$(get_var $i "$HOME/.avi2mp4.conf")"
done

for i in ${essential_commands[@]}; do
  type $i 1> /dev/null
  ERROR=$?
  e "$i not found in your \$PATH." $E_MISSING_CMD
done

if [ "$USE_NERO_AAC" = yes ]; then
  if [ "$NERO_OVERRIDE" ]; then
    echo Not checking for neroAacEnc because \$NERO_OVERRIDE is set.
    echo
  else
    type neroAacEnc 1> /dev/null
    ERROR=$?
    e "neroAacEnc not found in your \$PATH." $E_MISSING_CMD
  fi
else
  type faac 1> /dev/null
  ERROR=$?
  e "faac not found in your \$PATH." $E_MISSING_CMD
fi

if [ "$SBTL" = yes ]; then
  type sbtlpatch 1> /dev/null
  ERROR=$?
  e "sbtlpatch not found in your \$PATH." $E_MISSING_CMD
fi

IS_ONE=$(echo "$MAX_BITRATE>0" | bc --mathlib)
if [ "$IS_ONE" != 1 ]; then
  ERROR=1
  e "\$MAX_BITRATE invalid." $E_CFG
fi

for i in WORK_DIR TMP_DIR; do
  if [ -z "${!i}" ]; then
    ERROR=1
    e "\$$i is not set." $E_CFG
  fi
done

case "$VIDEO_CODEC" in
  xvid)
  RAW_VIDEO_EXTENSION=cmp
  CT_ENCODER_OPTS="pass=1"
  ;;
  x264)
  IS_ONE=$(echo "1<=$CT_X264_CRF && $CT_X264_CRF<=50" | bc --mathlib)
  if [ ! "$IS_ONE" = 1 ]; then
    ERROR=1
    e "\$CT_X264_CRF unset or out of range: 1 <= \$CT_X264_CRF <= 50" $E_CFG
  fi
  RAW_VIDEO_EXTENSION=h264
  CT_ENCODER_OPTS="crf=$CT_X264_CRF"
  ;;
  *)
  ERROR=1
  e "\$VIDEO_CODEC must be set to either \"xvid\" or \"x264\"." $E_CFG
  ;;
esac

for i in WORK_DIR TMP_DIR; do
  if [ ! -d "${!i}" ]; then
    ERROR=1
    e "\$$i does not exist or is not a directory." $E_CFG
  fi

  RND_ASCII=$(date '+%C%g_%m_%d_%H_%M_%S')_$RANDOM
  touch "${!i}/$RND_ASCII.test"
  ERROR=$?
  e "\$$i is not writable." $E_WRTBL
  rm "${!i}/$RND_ASCII.test"
done

if [ "$X264_ENCOPTS" ]; then
  X264_ENCOPTS=:$X264_ENCOPTS
fi

if [ "$XVID_ENCOPTS" ]; then
  XVID_ENCOPTS=:$XVID_ENCOPTS
fi

MAX_RES=$(echo "$MAX_RES" | grep --extended-regexp '^[0-9]+x[0-9]+$')
if [ -z "$MAX_RES" ]; then
  MAX_W=640
  MAX_H=480
else
  MAX_W=$(echo "$MAX_RES" | sed --regexp-extended 's/x[0-9]*//')
  MAX_H=$(echo "$MAX_RES" | sed --regexp-extended 's/[0-9]*x//')
fi

IS_NULL=$(echo "$MAX_W<16 || $MAX_H<16" | bc --mathlib)
if [ "$IS_NULL" != 0 ]; then
  ERROR=1
  e "\$MAX_RES needs to be greater than 16x16." $E_CFG
fi

if [ "$RENICE" ]; then
  type renice 1> /dev/null
  ERROR=$?
  e "renice requested but missing." $E_MISSING_CMD
  echo Altering script priority:
  echo
  renice $RENICE --pid $$
  ERROR=$?
  e "renice bailed out with exit status $ERROR." $E_CFG
  echo
fi

for i in CT_PERCENT CT_QUAL; do
  IS_ONE=$(echo "0<${!i} && ${!i}<=100" | bc --mathlib)
  if [ "$IS_ONE" != 1 ]; then
    ERROR=1
    e "\$$i unset or our of range: 0 < \$$i <= 100." $E_CFG
  fi
done

if [ "$#" = 0 ]; then
  echo -e "\033[1mUsage: $AVI2MP4 <file> [<another file>] ...\033[0m\n"
  exit 0
fi

TRACK_LANG=$(echo "$TRACK_LANG" | grep '^[a-z]\{3\}$')
if [ -n "$TRACK_LANG" ]; then
  TRACK_LANG=":lang=$TRACK_LANG"
else
  unset -v TRACK_LANG
fi

if [ "$MP4BOX_IPOD" = yes ]; then
  MP4BOX_IPOD="-ipod"
else
  unset -v MP4BOX_IPOD
fi

for i in "$@"; do
  clear_variables

  BASENAME=$(basename "$i" | sed --regexp-extended \
                                      's/^[ \t]+//;s/[ \t]+$//;s/[.]+[^.]+$//')
  if [ -z "$BASENAME" ]; then
    warn "Filename too weird, skipping conversion of:\n$i"
    continue
  fi

  FILENAME=$(basename "$i")
  DIRNAME=$(dirname "$i")

  # Some programs have issues with non-ASCII characters (e.g. MPlayer's pcm
  # writer and gpac). Feed them with ASCII file names.
  RND_ASCII=$(date '+%C%g_%m_%d_%H_%M_%S')_$RANDOM

  echo -e "File:\t\t\t\033[1m$FILENAME\033[0m\n"

  if [ ! -r "$i" ]; then
    warn "Input file does not exist or unreadable."
    continue
  fi

  if [ -e "$WORK_DIR/$BASENAME.mp4" ]; then
    echo -e "\aFile apparently already converted to MP4:" >&2
    echo -e "\$WORK_DIR/$BASENAME.mp4 exists." >&2
    echo -e "Skipping.\n" >&2
    continue
  fi

  if [ -e "$TMP_DIR/$BASENAME.lock" ]; then
    echo -e "\aConversion of input file already running." >&2
    echo -e "If this is not the case remove the stale lock file:" >&2
    echo -e "\$TMP_DIR/$BASENAME.lock" >&2
    echo -e "Skipping.\n" >&2
    continue
  fi

  echo -n "Creating lock file ... "
  touch "$TMP_DIR/$BASENAME.lock"
  ERROR=$?
  e "\nUnable to write lock file to \$TMP_DIR." $E_WRTBL
  echo -e "\tdone.\n"

  # Get properties of file
  touch "$TMP_DIR/${RND_ASCII}_myidentify.log"
  ERROR=$?
  e "Unable to write to:\n$TMP_DIR/${RND_ASCII}_myidentify.log" $E_WRTBL
  myidentify "$i" > "$TMP_DIR/${RND_ASCII}_myidentify.log"

  for j in ${mplayer_media_vars[@]}; do
    declare $j="$(get_var $j "$TMP_DIR/${RND_ASCII}_myidentify.log")"
    if [ ! "${!j}" ]; then
      warn "Input file not recognized as a valid media file.\nSkipping."
      rm_log_and_lock
      continue 2
    fi
  done

  for j in ID_VIDEO_WIDTH ID_VIDEO_HEIGHT ID_VIDEO_FPS ID_LENGTH ID_AUDIO_NCH
  do
    IS_NULL=$(echo "${!j}<=0" | bc --mathlib)
    if [ "$IS_NULL" = 1 ]; then
      warn "Input file properties invalid.\nSkipping."
      rm_log_and_lock
      continue 2
    fi
  done

  if echo "$ID_DEMUXER" | grep --quiet --ignore-case mpeg; then
    warn "MPEG container detected.\nSkipping. See README for explanation."
    rm_log_and_lock
    continue
  fi

  IS_ONE=$(echo "$ID_VIDEO_ASPECT==0" | bc --mathlib)
  if [ "$IS_ONE" = 1 ]; then
    echo -e "Video aspect not set. Assuming width:height.\n"
    ID_VIDEO_ASPECT=$(echo "$ID_VIDEO_WIDTH/$ID_VIDEO_HEIGHT" | bc --mathlib)
  fi

  if [ -r "$i.vf" ]; then
    VFOPTS=$(clean_line "$i.vf")
    if [ "$VFOPTS" ]; then
      VFOPTS=$VFOPTS,
      CropOrNot=$(echo "$VFOPTS" | grep --only-matching 'crop=' | wc --lines)
      if [ "$CropOrNot" -gt 1 ]; then
        warn "Cropping more than once not supported.\nSkipping."
        rm_log_and_lock
        continue
      fi
      CropOrNot=$(echo "$VFOPTS" | grep --extended-regexp --only-matching \
                                      'crop=[0-9]*:?[0-9]*' | sed 's/crop=//')
      CW=$(echo "$CropOrNot" | grep --extended-regexp --only-matching \
                                                                    '^[0-9]+')
      CH=$(echo "$CropOrNot" | grep --extended-regexp --only-matching \
                      '^[0-9]*:[0-9]+' | sed --regexp-extended 's/^[0-9]*://')
      if [ "$CW" ] || [ "$CH" ]; then
        echo Cropping detected. Calculating new aspect ratio.
        echo
        if [ ! "$CW" ] || [ "$CW" = 0 ] || [ "$CW" -gt "$ID_VIDEO_WIDTH" ]
        then
          CW=$ID_VIDEO_WIDTH
        fi
        if  [ ! "$CH" ] || [ "$CH" = 0 ] || [ "$CH" -gt "$ID_VIDEO_HEIGHT" ]
        then
          CH=$ID_VIDEO_HEIGHT
        fi
        ID_VIDEO_ASPECT=$(echo "($CW*$ID_VIDEO_ASPECT*$ID_VIDEO_HEIGHT)/\
                                        ($CH*$ID_VIDEO_WIDTH)" | bc --mathlib)
      fi
    fi
  fi

  if [ -r "$i.me" ]; then
    MENCODER_OPTS2=$(clean_line "$i.me")
    if [ ! -z "$MENCODER_OPTS2" ]; then
      echo Adding \"$MENCODER_OPTS2\" to mencoder calls.
      echo
    fi
  fi

  if [ -f "$i.p" ]; then
    if [ "$ID_VIDEO_FPS" = 29.970 ]; then
      ID_VIDEO_FPS=23.976
      echo User indicated content is progressive NTSC. Changing framerate from
      echo 29.970 to 23.976 fps.
      echo
    fi
  fi

  echo -e "Resolution:\t\t${CW:-$ID_VIDEO_WIDTH}x${CH:-$ID_VIDEO_HEIGHT}"
  echo -e "FPS:\t\t\t$ID_VIDEO_FPS"
  echo -en "Aspect:\t\t\t"
  echo "$ID_VIDEO_ASPECT" | xargs printf "%1.4f\n\n"

  IS_ONE=$(echo "$ID_VIDEO_FPS>30" | bc --mathlib)
  if [ "$IS_ONE" = 1 ]; then
    warn "Input framerate is greater than 30 per second.\nSkipping."
    rm_log_and_lock
    continue
  fi

  if [ "$VIDEO_CODEC" = x264 ]; then
    # Set keyint/keyint_min depending on $ID_VIDEO_FPS
    KEYINT_MIN=$(echo "$ID_VIDEO_FPS" | xargs printf "%1.0f\n")
    KEYINT=$(echo "$ID_VIDEO_FPS*10" | bc --mathlib | xargs printf "%1.0f\n")
    KEYINT_CMD=:keyint=$KEYINT:keyint_min=$KEYINT_MIN
    ENCODER_OPTS=$X264_ENCOPTS
  else
    ENCODER_OPTS=$XVID_ENCOPTS
  fi

  # Mencoder likes accurate FPS
  if [ "$ID_VIDEO_FPS" = 23.976 ]; then
    MFPS=24000/1001
    elif [ "$ID_VIDEO_FPS" = 29.970 ]; then
      MFPS=30000/1001
    else
      MFPS=$ID_VIDEO_FPS
  fi

  for ((OUT_HORIZ=$MAX_W, LEGIT=0; OUT_HORIZ>=16; OUT_HORIZ-=16)); do
    # We don't want to scale up the video
    if [ "$OUT_HORIZ" -gt "${CW:-$ID_VIDEO_WIDTH}" ]; then
      continue
    fi

    MULTIPLIER=$(echo "$OUT_HORIZ/($ID_VIDEO_ASPECT*16)" | bc --mathlib | \
                                                        xargs printf "%1.0f\n")
    OUT_VERT=$(echo "16*$MULTIPLIER" | bc --mathlib)

    IS_NULL=$(echo "$OUT_VERT<16" | bc --mathlib)
    if [ "$IS_NULL" = 1 ]; then
      warn "Output height too small.\nSkipping."
      rm_log_and_lock
      continue 2
    fi

    if [ "$OUT_VERT" -gt "$MAX_H" ]; then
      continue
    fi

    if [ "$OUT_VERT" -gt "${CH:-$ID_VIDEO_HEIGHT}" ]; then
      continue
    fi

    # Seconds
    CompTestDuration=$(echo "($ID_LENGTH/100)*$CT_PERCENT" | bc --mathlib | \
                                                        xargs printf "%1.0f\n")
    # Frames
    RangeLength=$(echo "$ID_VIDEO_FPS*10" | bc --mathlib | xargs printf \
                                                                    "%1.0f\n")
    # Make sure we round up (bc rounds down everything)
    NumberOfRanges=$(echo "scale=0; (($ID_VIDEO_FPS*$CompTestDuration)/\
                                              $RangeLength)+1" | bc --mathlib)
    RangeBoundary=$(echo "$ID_LENGTH/$NumberOfRanges" | bc --mathlib | xargs \
                                                              printf "%1.0f\n")

    echo -ne "\033[1mCompressibility test:\033[0m\t0%"
    TotalSize=0
    j=$NumberOfRanges
    while [ "$j" -gt 0 ]; do
      StartSec=$(echo "($j-1)*$RangeBoundary" | bc --mathlib)
      mencoder "$i" -oac pcm -of rawvideo -ovc $VIDEO_CODEC -vf \
                                "${VFOPTS}scale=$OUT_HORIZ:$OUT_VERT,harddup" \
                                          -ofps $MFPS -${VIDEO_CODEC}encopts \
                                  "$CT_ENCODER_OPTS$KEYINT_CMD$ENCODER_OPTS" \
                            -o "$TMP_DIR/${RND_ASCII}_comp.raw" -passlogfile \
                                            "$TMP_DIR/${RND_ASCII}_comp.log" \
                                          $MENCODER_OPTS $MENCODER_OPTS2 -ss \
                                              $StartSec -frames $RangeLength \
                                          &> "$TMP_DIR/${RND_ASCII}_menc.out"
      ERROR=$?
      if [ ! "$ERROR" = 0 ]; then
        echo -e "\n"
        cat "$TMP_DIR/${RND_ASCII}_menc.out"
        e "$CMD_ABORT" $E_CMD
      fi

      FileSize=$(stat -c '%s' "$TMP_DIR/${RND_ASCII}_comp.raw")

      # Byte
      let "TotalSize += FileSize"
      let "j -= 1"

      Percent=$(echo "(100/$NumberOfRanges)*($NumberOfRanges-$j)" | bc \
                                            --mathlib | xargs printf "%1.0f\n")

      echo -ne "\r\033[1mCompressibility test:\033[0m\t$Percent%"
    done

    # kbit/s
    CompTestBitrate=$(echo "($TotalSize*8)/($CompTestDuration*1000)" | bc \
                                                                    --mathlib)
    BITRATE=$(echo "($CompTestBitrate/100)*$CT_QUAL" | bc --mathlib | xargs \
                                                              printf "%1.0f\n")

    IS_ONE=$(echo "$MAX_BITRATE>=$BITRATE" | bc --mathlib)
    if [ "$IS_ONE" = 1 ]; then
      echo -e "\r\033[1mCompressibility test:\tSuccess\033[0m\n"
      LEGIT=1
      break
    else
      echo -en "\n\n\$MAX_BITRATE not enough for resolution "
      echo -e "${OUT_HORIZ}x$OUT_VERT.\n"
    fi
  done

  rm --force "$TMP_DIR/${RND_ASCII}_comp.raw" \
                                            "$TMP_DIR/${RND_ASCII}_comp.log" \
                                              "$TMP_DIR/${RND_ASCII}_menc.out"

  if [ "$LEGIT" = 0 ]; then
    warn "No adequate combination of bitrate and resolution found.\nSkipping."
    rm_log_and_lock
    continue
  fi

  echo -e "Output resolution:\t${OUT_HORIZ}x$OUT_VERT"
  echo -e "Video bitrate:\t\t$BITRATE kbit/s\n"

  if [ "$SUBTITLES" = yes ]; then
    if [ -r "$DIRNAME/$BASENAME.srt" ]; then
      cp --force "$DIRNAME/$BASENAME.srt" "$TMP_DIR/${RND_ASCII}_srt.srt"
      ERROR=$?
      e "Copying SRT subtitle failed." $E_WRTBL
      SUBS_SRT="-add ${RND_ASCII}_srt.ttxt$TRACK_LANG"
      HAS_TTXT=yes
    fi
    if [ -r "$DIRNAME/$BASENAME.ttxt" ]; then
      cp --force "$DIRNAME/$BASENAME.ttxt" "$TMP_DIR/${RND_ASCII}_ttxt.ttxt"
      ERROR=$?
      e "Copying TTXT subtitle failed." $E_WRTBL
      SUBS_TTXT="-add ${RND_ASCII}_ttxt.ttxt$TRACK_LANG"
      HAS_TTXT=yes
    fi
    if [[ -r "$DIRNAME/$BASENAME.idx" && -r "$DIRNAME/$BASENAME.sub" ]]; then
      cp --force "$DIRNAME/$BASENAME.idx" "$TMP_DIR/${RND_ASCII}_vob.idx"
      ERROR=$?
      e "Copying VobSub subtitle (.idx) failed." $E_WRTBL
      cp --force "$DIRNAME/$BASENAME.sub" "$TMP_DIR/${RND_ASCII}_vob.sub"
      ERROR=$?
      e "Copying VobSub subtitle (.sub) failed." $E_WRTBL
      SUBS_VOBSUB="-add ${RND_ASCII}_vob.idx"
    elif [ -r "$DIRNAME/$BASENAME.sub" ]; then
      cp --force "$DIRNAME/$BASENAME.sub" "$TMP_DIR/${RND_ASCII}_sub.sub"
      ERROR=$?
      e "Copying SUB subtitle failed." $E_WRTBL
      SUBS_SUB="-add ${RND_ASCII}_sub.ttxt$TRACK_LANG"
      HAS_TTXT=yes
    fi
  fi

  for j in 1st 2nd; do
    if [ "$j" = 1st ]; then
      OUTFILE=/dev/null
    else
      OUTFILE="$TMP_DIR/${RND_ASCII}_menc.avi"
    fi

    echo -e "\033[1mMEncoder $j pass:\033[0m\n"
    mencoder "$i" -oac pcm -ovc $VIDEO_CODEC -vf \
                                "${VFOPTS}scale=$OUT_HORIZ:$OUT_VERT,harddup" \
                                          -ofps $MFPS -${VIDEO_CODEC}encopts \
              "pass=${j/%[stnd]*/}:bitrate=$BITRATE$KEYINT_CMD$ENCODER_OPTS" \
              -o "$OUTFILE" -passlogfile "$TMP_DIR/${RND_ASCII}_menc.log" \
                                                $MENCODER_OPTS $MENCODER_OPTS2
    ERROR=$?
    echo
    e "$CMD_ABORT" $E_CMD
  done

  echo -e "\033[1mExtracting audio:\033[0m\n"
  mplayer "$TMP_DIR/${RND_ASCII}_menc.avi" -vc null -vo null -ao \
                              pcm:fast:file="$TMP_DIR/${RND_ASCII}_mplay.wav" \
                          -noconsolecontrols -nolirc -nojoystick -nomouseinput
  ERROR=$?
  echo
  e "$CMD_ABORT" $E_CMD

  echo -e "\033[1mEncoding audio:\033[0m\n"
  if [ "$USE_NERO_AAC" = yes ]; then
    ${NERO_OVERRIDE:-neroAacEnc} $NAAC_ENCOPTS -of \
                                          "$TMP_DIR/${RND_ASCII}_aac.m4a" -if \
                                              "$TMP_DIR/${RND_ASCII}_mplay.wav"
    ERROR=$?
    echo
    e "$CMD_ABORT" $E_CMD
  else
    faac -o "$TMP_DIR/${RND_ASCII}_aac.m4a" \
                                $FAAC_ENCOPTS "$TMP_DIR/${RND_ASCII}_mplay.wav"
    ERROR=${?}
    echo
    e "$CMD_ABORT" $E_CMD
  fi

  echo -e "\033[1mApplying ReplayGain:\033[0m\n"
  aacgain -r -k "$TMP_DIR/${RND_ASCII}_aac.m4a"
  ERROR=${?}
  echo
  e "$CMD_ABORT" $E_CMD

  # MP4Box often doesn't behave when it comes to file and directory names
  # Workaround: Use ASCII file names and change to the temporary directory

  cd "$TMP_DIR"
  
  # Convert text subtitles to 3GPP text so we can change the header a bit

  # 20% of video height
  SUB_HEIGHT=$(echo "($OUT_VERT/100)*20" | bc --mathlib | xargs printf \
                                                                    "%1.0f\n")
  # Y translation = video height - subtitle height
  SUB_Y_TRANS=$(echo "${OUT_VERT}-${SUB_HEIGHT}" | bc --mathlib | xargs \
                                                              printf "%1.0f\n")

  for j in sub srt; do
    if [[ "$SUBTITLES" = yes && -r ${RND_ASCII}_${j}.${j} ]]; then
      if [ "$j" = sub ]; then
        echo -e "\033[1mConverting SUB subtitle to 3GPP:\033[0m\n"
      else
        echo -e "\033[1mConverting SRT subtitle to 3GPP:\033[0m\n"
      fi
      MP4Box -fps $ID_VIDEO_FPS -ttxt ${RND_ASCII}_${j}.${j}
      ERROR=$?
      echo
      e "$CMD_ABORT" $E_CMD

      # Change width, height and Y translation
      for k in width height translation_y; do
        case "$k" in
          width)
          VAL="$OUT_HORIZ"
          ;;
          height)
          VAL="$SUB_HEIGHT"
          ;;
          translation_y)
          VAL="$SUB_Y_TRANS"
          ;;
        esac

        TAG=TextStreamHeader

        sed --in-place --regexp-extended \
                          \/\^\<$TAG\/s\/$k\=\"\[0\-9\]\*\"\/$k\=\"$VAL\"\/ \
                                                        ${RND_ASCII}_${j}.ttxt
      done 
    fi
  done

  echo -e "\033[1mExtracting raw video:\033[0m\n"
  MP4Box -aviraw video ${RND_ASCII}_menc.avi
  ERROR=$?
  echo
  e "$CMD_ABORT" $E_CMD

  echo -e "\033[1mMuxing:\033[0m\n"
  MP4Box $MP4BOX_IPOD -fps $ID_VIDEO_FPS -nodrop -itags name="$BASENAME" \
                             -add ${RND_ASCII}_menc_video.$RAW_VIDEO_EXTENSION \
                                          -add ${RND_ASCII}_aac.m4a$TRACK_LANG \
                                   $SUBS_SRT $SUBS_VOBSUB $SUBS_SUB $SUBS_TTXT \
                                              -tmp . -new ${RND_ASCII}_gpac.mp4
  ERROR=$?
  echo
  e "$CMD_ABORT" $E_CMD

  # Right back to where we started
  cd "$START_DIR"

  if [[ "$SBTL" = yes && "$HAS_TTXT" = yes ]]; then
    echo -e "\033[1mChanging text subtitle handler type:\033[0m\n"
    sbtlpatch -w "$TMP_DIR/${RND_ASCII}_gpac.mp4"
    ERROR=$?
    echo
    e "$CMD_ABORT" $E_CMD
  fi

  echo -en "\033[1mCopying to \$WORK_DIR ...\033[0m"
  mv "$TMP_DIR/${RND_ASCII}_gpac.mp4" "$WORK_DIR/$BASENAME.mp4"
  ERROR=$?
  e "$CMD_ABORT" $E_CMD
  echo -e "\033[1m done.\033[0m\n"

  echo -ne "\033[1mCleaning up ...\033[0m"
  rm --force "$TMP_DIR/$BASENAME.lock" "$TMP_DIR/${RND_ASCII}"_*
  ERROR=$?
  e "$CMD_ABORT" $E_CMD
  echo -e "\033[1m done.\033[0m\n"

  echo -e "\033[1mEncoding $BASENAME.mp4 finished.\033[0m\n"
  continue
done

exit 0

# vim: set expandtab shiftwidth=2 tabstop=2:
