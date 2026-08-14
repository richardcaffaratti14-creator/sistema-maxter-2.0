@echo off
setlocal enableextensions enabledelayedexpansion

cd %1


for /R . %%f in (*.jpg) do (

	SET CurrDir=%%~df%%~pf
	SET DestDir=!CurrDir:\fotos\=\img_cache\fotos\!
	IF NOT EXIST "!DestDir!" mkdir "!DestDir!"
	echo Generando thumbnails: %%f
	
	IF NOT EXIST "!DestDir!%%~nf_1200x800.jpg" mogrify -auto-orient -thumbnail 1200x800 -format jpg -quality 100 -write "!DestDir!%%~nf_1200x800.jpg"  "%%f"
	IF NOT EXIST "!DestDir!%%~nf_500x400.jpg" mogrify -auto-orient -thumbnail 500x400 -format jpg -quality 100 -write "!DestDir!%%~nf_500x400.jpg"  "%%f"
	IF NOT EXIST "!DestDir!%%~nf_180x190.jpg" mogrify -auto-orient -thumbnail 180x190 -format jpg -quality 100 -write "!DestDir!%%~nf_180x190.jpg"  "%%f"

REM	mogrify -thumbnail 1200x800 -format jpg -quality 100 -write ..\img_cache\fotos\%%~nf_1200x800.jpg  %%f
REM	mogrify -thumbnail 180x190 -format jpg -quality 100 -write ..\img_cache\fotos\%%~nf_180x190.jpg  %%f
)

pause