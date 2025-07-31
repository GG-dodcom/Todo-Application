# Yii2 Todo App

## Requirements

- PHP 8.2.12
- MySQL or MariaDB
- Composer

## Setup Instructions

1. Clone the project:

```

git clone https://github.com/GG-dodcom/Todo-Application.git
cd Todo-Application

```

2. Install dependencies:

```

composer install

```

3. Configure DB in `config/db.php`.

4. Run migration:

```

php yii migrate

```

5. Start dev server:

```

php yii serve

```

6. Access app at:

[http://localhost:8080](http://localhost:8080)

## Features

- Add, view, update, delete tasks
- Mark tasks complete/incomplete
- Due date tracking
- Simple UI with Bootstrap
