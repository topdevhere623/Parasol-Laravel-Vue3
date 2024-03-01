FROM node:14.19-alpine as npm-build-stage

WORKDIR /app

#add `/app/node_modules/.bin` to $PATH
ENV PATH /app/node_modules/.bin:$PATH

COPY package*.json webpack.mix.js ./
COPY public public
COPY resources resources

RUN npm install
