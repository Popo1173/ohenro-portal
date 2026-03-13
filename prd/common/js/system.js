
  let lang_code = null;
  let add_favorite_mes = '';
  let del_favorite_mes = '';
  let not_member_mes = '';

  //初期値設定
  function set_init(lang, add_mes, del_mes, no_mes) {
    lang_code = lang;
    add_favorite_mes = add_mes;
    del_favorite_mes = del_mes;
    not_member_mes = no_mes;
  }

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

  // ボタンをすべて取得
  const buttons = document.querySelectorAll('.favorite-button');

  // 各ボタンにイベントリスナーを追加
  buttons.forEach(button => {
    button.addEventListener('click', (e) => {
      // クリックされたボタン自身を取得
      const element = e.currentTarget;

      if (element.dataset.id == "no-member") {
        alert(not_member_mes);
      }
      else if (element.classList.contains('is-active')) {
        cancel_favorite(element.dataset.id, 1, lang_code);
        element.classList.toggle('is-active');
      }
      else {
        add_favorite(element.dataset.id, 1, lang_code);
        element.classList.toggle('is-active');
      }
    });
  });

  //お気に入り追加時
  function add_favorite(temple_num, movie_num, lang_code) {
    const data = { md: "movie_favorite", lang_code: lang_code, temple_num: temple_num, movie_num: movie_num };

    fetch('/user.php', {
      method: 'POST',
      body: new URLSearchParams(data)
    })
    .then(res => res.text())
    .then(() => {
      alert(add_favorite_mes);
    })
    .catch(err => console.error(err));

  }

  //お気に入り解除時 (札所一覧)
  function cancel_favorite(temple_num, movie_num, lang_code) {
    const data = { md: "delete_favorite", lang_code: lang_code, temple_num: temple_num, movie_num: movie_num };

    fetch('/user.php', {
      method: 'POST',
      body: new URLSearchParams(data)
    })
    .then(res => res.text())
    .then(() => {
      alert(del_favorite_mes);
    })
    .catch(err => console.error(err));
  }

  //お気に入り解除時 (マイページお気に入り一覧)
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

