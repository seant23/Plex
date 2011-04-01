<?php



class FFMpeg
{
	static public function getInfo($filePath)
	{
		$media_file = new ffmpeg_movie($filePath);

		@$info['duration']=@$media_file->getDuration();
		@$info['frame_count']=@$media_file->getFrameCount();
		@$info['frame_rate']=@$media_file->getFrameRate();
		@$info['file_name']=@$media_file->getFilename();
		@$info['comments']=@$media_file->getComment();
		@$info['title']=@$media_file->getTitle();
		@$info['artist']=@$media_file->getArtist();
		@$info['copyright']=@$media_file->getCopyright();
		@$info['genre']=@$media_file->getGenre();
		@$info['track_number']=@$media_file->getTrackNumber();
		@$info['year']=@$media_file->getYear();
		@$info['frame_height']=@$media_file->getFrameHeight();
		@$info['frame_width']=@$media_file->getFrameWidth();
		@$info['pixel_format']=@$media_file->getPixelFormat();
		@$info['bit_rate']=@$media_file->getBitRate();
		@$info['video_bit_rate']=@$media_file->getVideoBitRate();
		@$info['audio_bit_rate']=@$media_file->getAudioBitRate();
		@$info['audio_sample_rate']=@$media_file->getAudioSampleRate();
		@$info['video_codec']=@$media_file->getVideoCodec();
		@$info['audio_codec']=@$media_file->getAudioCodec();
		@$info['audio_channels']=@$media_file->getAudioChannels();

		foreach($info as $key=>$val)
		{
			if(!$val)
			unset($info[$key]);
		}

		return $info;
	}
	

}