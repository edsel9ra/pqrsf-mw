FROM node:22-bookworm

ARG UID=1000
ARG GID=1000

ENV HOME="/home/node"
ENV PNPM_STORE_DIR="/home/node/.pnpm-store"
ENV PATH="/usr/local/bin:/usr/local/sbin:/usr/sbin:/usr/bin:/sbin:/bin"

RUN npm install -g pnpm@10 \
    && mkdir -p /var/www "$PNPM_STORE_DIR" \
    && chown -R ${UID}:${GID} /var/www /home/node "$PNPM_STORE_DIR"

WORKDIR /var/www