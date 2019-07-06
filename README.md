# Konfigürasyon:
Delimiter(controller ve fonksiyon ayracı) ve controller için ana dizin
değiştirmek için,<br>
```src\RR\::DELIMITER-ROOT_DIR```

# Router Örnek:
Parametresiz **callback**'ler ve parametresiz **Controller**
fonksiyonları şu an aktif. Örnekleri ise;
## Controller:<br>
``\AST\Router::get('/home', 'HomeController:index');``<br>
> Controller ismi ve fonksiyon isimleri **:** ile ayrılmakta.
## Callback:<br>
``\AST\Router::get('/welcome', function () {
      return "You're welcome";
  });``
  
  
  
  # TODO: Router
  - [x] Controller sınıfı yazılacak.
  - [] Controller parametreleri kullanılacak.
  - [] Request sınıfı yazılacak.
  - [] Dependency sınıfı yazılacak.
  - [] Facade ve Service yazılacak
