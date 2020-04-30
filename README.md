# Garni Weather Station server PHP script
This simple php script gets data values from your Garni Meteo Station and coverts it to JSON file on your server. You need to setup few things.

## Instalation
In your Garni device, you should add your domain where you will save you JSON file, for example **myweb.com**

1. use ftp and connect to your server (domain myweb.com)
2. make directory **weatherstation**
3. upload script **updateweatherstation.php** to **/weatherstation** directory
4. make directory **data** in **/weatherstation**
5. change **/data** directory attributes to **755**

## Testing
Check your php script if everything works OK. Go to your website:
> myweb.com/weatherstation/updateweatherstation.php
You should find file **garni.json** in data directory with these information
>{"manufacturer":"Garni"}

## Known bugs
- there is problem if you use SSL on your server. You need to use port 80
- in JSON file there are last data values, no history (but I can make it)
