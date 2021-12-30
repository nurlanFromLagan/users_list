<?php $this->layout('layoutUsers', ['title' => 'Загрузить аватар']) ?>

<main id="js-page-content" role="main" class="page-content mt-3">
    <div class="subheader">
        <h1 class="subheader-title">
            <i class='subheader-icon fal fa-image'></i> Загрузить аватар
        </h1>
    </div>

        <?php if (flash()->message()): ?>
        <!--   Здесь выводится сообщение об ошибке в случае некорректно введенных данных    -->
        <?php echo flash()->display(); endif; ?>

    <form action="/uploadAvatar/<?=$user['id']; ?>" enctype="multipart/form-data" method="post">
        <div class="row">
            <div class="col-xl-6">
                <div id="panel-1" class="panel">
                    <div class="panel-container">
                        <div class="panel-hdr">
                            <h2>Текущий аватар</h2>
                        </div>
                        <div class="panel-content">
                            <div class="form-group">
                                <img src="/images/avatars/<?=$user['image'] ?>" alt="" class="img-responsive" width="200">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="example-fileinput">Выберите аватар</label>
                                <input type="file" name="image" id="example-fileinput" class="form-control-file">
                            </div>


                            <div class="col-md-12 mt-3 d-flex flex-row-reverse">
                                <button class="btn btn-warning">Загрузить</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>