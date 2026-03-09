
  //言語選択時
  function select_language(lang_code) {
    const data = { md: "select_language", lang_code: lang_code };

    fetch('/user.php', {
      method: 'POST',
      body: new URLSearchParams(data)
    })
    .then(res => res.text())
    .then(() => {
    })
    .catch(err => console.error(err));
  }

  //お気に入りボタン押下時
  function click_favorite(flag, temple_num, movie_num, lang_code) {
    if (!flag) {
      add_favorite(temple_num, movie_num, lang_code);
      //ボタン変更処理

    }
    else {
      delete_favorite(temple_num, movie_num, lang_code);
      //ボタン変更処理

    }
  }

  //お気に入り追加時
  function add_favorite(temple_num, movie_num, lang_code) {
    const data = { md: "delete_favorite", lang_code: lang_code, temple_num: temple_num, movie_num: movie_num };

    fetch('/user.php', {
      method: 'POST',
      body: new URLSearchParams(data)
    })
    .then(res => res.text())
    .then(() => {
      alert("お気に入りに追加いたしました。");
    })
    .catch(err => console.error(err));

  }

  //お気に入り解除時
  function delete_favorite(msg, temple_num, movie_num, lang_code) {
    //let result = confirm("お気に入りを解除します。よろしいですか？");
    let result = confirm(msg);
    if (result) {
      const data = { md: "delete_favorite", lang_code: lang_code, temple_num: temple_num, movie_num: movie_num };

      fetch('/user.php', {
        method: 'POST',
        body: new URLSearchParams(data)
      })
      .then(res => res.text())
      .then(() => {
        location.reload();
      })
      .catch(err => console.error(err));
    }
  }

