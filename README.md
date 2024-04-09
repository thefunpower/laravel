#  安装

~~~
composer require thefunpower/laravel
~~~

# 数据表

~~~
Schema::create('config', function (Blueprint $table) {
    $table->id();
    $table->string('title')->index(); 
    $table->text('body')->nullable(); 
    $table->unsignedInteger('created_at'); 
    $table->unsignedInteger('updated_at');  
});
~~~