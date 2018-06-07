src_video=$1
tmp_video=$2
des_video=$3
/usr/local/bin/ffmpeg -y -i ${src_video} -f mp4 -vcodec mpeg4 -r 25 -b 1024k -ab 128k -ac 2 -async 1 -strict -2 ${tmp_video} 2>/dev/null & 
cp ${tmp_video} ${des_video}