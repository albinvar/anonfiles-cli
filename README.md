<h1 align="center"> Anonfiles CLI </h1> <br>
<p align="center">
  <a href="https://anonfiles.com/">
    <img src="https://i.ibb.co/kJY81TL/Anon-Files.png" alt="logo" width="190" border="0">
  </a>

<br> 

<img src="https://img.shields.io/github/v/release/albinvar/anonfiles-cli">
<img src="https://img.shields.io/github/repo-size/albinvar/anonfiles-cli">
<a href="LICENSE"><img src="https://img.shields.io/apm/l/Github"></a>
</p>

## Table of Contents

- [Introduction](#introduction)
- [Features](#features)
- [Installation](#installation)
- [Contributing](#contributing)
- [License](#license)

## Introduction

Anon Files CLI can upload and download files from anonfiles.com API within your command line interface.

## Features

- Upload your files directly from CLI.
- Rename before uploading.
- Download your files directly to a specified folder. 

## Installation

- download the phar file to your machine.

##### using CURL

```curl
curl -s https://github.com/albinvar/anonfiles-cli/raw/main/builds/anonfiles -o anonfiles

```
##### using wget
```wget
wget https://github.com/albinvar/anonfiles-cli/raw/main/builds/anonfiles -O anonfiles
```

#####  composer `(on construction)`
Remember, installing with composer requires each and every libraries to be downloaded first.

```
composer global require albinvar/anonfiles-cli
```

## Usage

Using anonfiles CLI is very simple. 

its better to include the cli to your bin of your specified OS.

```
php anonfiles
```

##### Upload a file

```
php anonfiles upload image.jpeg
```

you can also upload your files through socks5  proxy. by default its configured at `socks5h://127.0.0.1:9050`. It will be customisable in future updates.

you may now upload the files using  `--tor` option.

```
php anonfiles upload folder/image.jpeg --tor
```


##### Download a file

downloading a file is simple,

```
php anonfiles download https://anonfiles.com/u1C0ebc4b0
```

- the link should be only from anonfiles.com

you may use `--path` option to change your download path.

```
php anonfiles download https://anonfiles.com/u1C0ebc4b0 --path="/sdcard/downloads"
```

similarly, you can also download your files using tor socks proxy using `--tor` option.

```
php anonfiles download https://anonfiles.com/u1C0ebc4b0  --tor
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

The project is certified using [MIT License](LICENSE)
