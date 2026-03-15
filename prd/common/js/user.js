
  //APIの読み込み
  let tag = document.createElement('script');
  tag.src = "https://www.youtube.com/iframe_api";
  let firstScriptTag = document.getElementsByTagName('script')[0];
  firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

  let lang_code = null;
  let temple_num = null;
  let movie_num = null;

  function set_movie_id(lang = null, temple = null, movie = null) {
    lang_code = lang;
    temple_num = temple;
    movie_num = movie;
  }

  let player;
  function onYouTubeIframeAPIReady() {
    var playerElement = document.getElementById('player');
    var video_id = playerElement.getAttribute('data-video-id');

    player = new YT.Player('player', {
      height: '360',
      width: '640',
      videoId: video_id, // 動画IDを指定
      playerVars: {
        'rel': 0, // 関連動画を自チャンネルのみに制限
        'modestbranding': 1 // YouTubeロゴを控えめにする
      }
    });
  }

  //動画エリアクリック時
  document.getElementById('youtube').addEventListener('click', (e) => {
    //再生、停止
    btn_pause();
  });

  //ミュート
  function btn_mute() {
    element = document.querySelector('.movie__mute');
    if (player) {
      if (player.isMuted() == 1) {
        player.unMute();
      }
      else {
        player.mute();
      }
      element.classList.toggle('is-active');
    }
  }

  //再生、停止
  function btn_pause() {
    element = document.querySelector('.movie__pause');
    if (player) {
      if (player.getPlayerState() == 1) {
        player.pauseVideo();
      }
      else {
        player.playVideo();
      }
      element.classList.toggle('is-active');
    }
  }

  // 1秒ごとに再生時間をチェックするタイマー
  setInterval(function() {
    if (player && player.getPlayerState() == YT.PlayerState.PLAYING) {
      let currentTime = player.getCurrentTime();
      if (currentTime >= 30) { 
        player.pauseVideo();
        player = null;
        element = document.querySelector('.movie__announcement');
        element.classList.toggle('is-active');
      }
    }
  }, 1000);

