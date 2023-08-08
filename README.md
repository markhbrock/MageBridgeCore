# MageBridgeCore

[![Build Status][ci-badge]][ci]

[ci]: https://github.com/akunzai/MageBridgeCore/actions?query=workflow%3ACI
[ci-badge]: https://github.com/akunzai/MageBridgeCore/workflows/CI/badge.svg

This is the repository for the MageBridge Open Core. It includes the main Joomla! component as well as vital plugins, plus the Magento extension. With it, a fully bridged site can be built.

## Requirements

- PHP >= 8.1
- [Composer](https://getcomposer.org/)
- [Joomla!](https://www.joomla.org/) 3.x
- [OpenMage](https://github.com/OpenMage/magento-lts) 19.x

## Set up development containers

> [mkcert](https://github.com/FiloSottile/mkcert) needs to be installed

```sh
# append /etc/hosts
echo '127.0.0.1 store.example.test www.example.test' | sudo tee -a /etc/hosts

# install the local CA in the system trust store
mkcert -install

# generate locally-trusted development certificates
mkcert -cert-file .devcontainer/cert.pem -key-file .devcontainer/key.pem '*.example.test'
```
