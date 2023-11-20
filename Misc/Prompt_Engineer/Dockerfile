FROM node:bookworm-slim

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

ENV PORT=8080
ENV OPENAI_API_KEY=nop

EXPOSE 8080

CMD ["npm", "start"]