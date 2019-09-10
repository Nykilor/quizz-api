# quizz-api
A portfolio project.
Quick setup:
 - Clone the project and download dependencies
 - Setup the DATABASE_URL credentials in .env
 - Setup the JWT_PASSPHRASE in .env
 - Go into CMD and the directory of the folder and run commands bellow.
 - php bin\console doctrine:database:create (creates DB)
 - php bin\console doctrine:schema:update --force (creates tables)
 - php bin\console doctrine:fixtures:load (loads dummy data)
 - php bin\console server:run (runs server)
 - openssl genpkey -out config/jwt/private.pem -pass stdin -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
 - openssl pkey -in config/jwt/private.pem -passin stdin -out config/jwt/public.pem -pubout 
 
OpenSSL for windows: https://slproweb.com/products/Win32OpenSSL.html download nad set up the system variable for it https://www.howtogeek.com/51807/how-to-create-and-use-global-system-environment-variables/
