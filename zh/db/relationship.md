# 模型关联

## 定义关联

关联在 `Hyperf` 模型类中以方法的形式呈现。如同 `Hyperf` 模型本身，关联也可以作为强大的 `查询语句构造器` 使用，提供了强大的链式调用和查询功能。例如，我们可以在 role 关联的链式调用中附加一个约束条件：

```php
$user->role()->where('level', 1)->get();
```

### 一对一

一对一是最基本的关联关系。例如，一个 `User` 模型可能关联一个 `Role` 模型。为了定义这个关联，我们要在 `User` 模型中写一个 `role` 方法。在 `role` 方法内部调用 `hasOne` 方法并返回其结果:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public function role()
    {
        return $this->hasOne(Role::class, 'user_id', 'id');
    }
}
```

`hasOne` 方法的第一个参数是关联模型的类名。一旦定义了模型关联，我们就可以使用 `Hyperf` 动态属性获得相关的记录。动态属性允许你访问关系方法就像访问模型中定义的属性一样：

```php
$role = User::query()->find(1)->role;
```

### 一对多

『一对多』关联用于定义单个模型拥有任意数量的其它关联模型。例如，一个作者可能写有多本书。正如其它所有的 `Hyperf` 关联一样，一对多关联的定义也是在 `Hyperf` 模型中写一个方法：

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public function books()
    {
        return $this->hasOne(Book::class, 'user_id', 'id');
    }
}
```

记住一点，`Hyperf` 将会自动确定 `Book` 模型的外键属性。按照约定，`Hyperf` 将会使用所属模型名称的 『snake case』形式，再加上 _id 后缀作为外键字段。因此，在上面这个例子中，`Hyperf` 将假定 `User` 对应到 `Book` 模型上的外键就是 book_id。

一旦关系被定义好以后，就可以通过访问 `User` 模型的 `books` 属性来获取评论的集合。记住，由于 Hyperf 提供了『动态属性』 ，所以我们可以像访问模型的属性一样访问关联方法：

```php
$books = User::query()->find(1)->books;

foreach ($books as $book) {
    //
}
```

当然，由于所有的关联还可以作为查询语句构造器使用，因此你可以使用链式调用的方式，在 books 方法上添加额外的约束条件：

```php
$book = User::query()->find(1)->books()->where('title', '一个月精通Hyperf框架')->first();
```

## 预加载

当以属性方式访问 `Hyperf` 关联时，关联数据「懒加载」。这着直到第一次访问属性时关联数据才会被真实加载。不过 `Hyperf` 能在查询父模型时「预先载入」子关联。预加载可以缓解 N + 1 查询问题。为了说明 N + 1 查询问题，考虑 `User` 模型关联到 `Role` 的情形：

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public function role()
    {
        return $this->hasOne(Role::class, 'user_id', 'id');
    }
}
```

现在，我们来获取所有的用户及其对应角色

```php
$users = User::query()->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

此循环将执行一个查询，用于获取全部用户，然后为每个用户执行获取角色的查询。如果我们有 10 个人，此循环将运行 11 个查询：1 个用于查询用户，10 个附加查询对应的角色。

谢天谢地，我们能够使用预加载将操作压缩到只有 2 个查询。在查询时，可以使用 with 方法指定想要预加载的关联：

```php
$users = User::query()->with('role')->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

在这个例子中，仅执行了两个查询

```
SELECT * FROM `user`;

SELECT * FROM `role` WHERE id in (1, 2, 3, ...);
```