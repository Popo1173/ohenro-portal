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
      },
      events: {
        'onStateChange': onPlayerStateChange
      }
    });
  }

  // 状態が変化した時に呼ばれる関数
  function onPlayerStateChange(event) {
    if (event.data == YT.PlayerState.ENDED) {
      // 終了後の処理
      if (temple_num) {
        const data = { md: "movie_end", lang_code: lang_code, temple_num: temple_num, movie_num: movie_num };
        fetch('/user.php', {
          method: 'POST',
          body: new URLSearchParams(data)
        })
        .then(res => res.text())
        .then(() => {
        })
        .catch(err => console.error(err));
      }
    }
  }
