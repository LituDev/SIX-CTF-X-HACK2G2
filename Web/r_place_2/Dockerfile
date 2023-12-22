FROM rust:1.73-slim as builder 

RUN apt-get update && \
    apt-get install -y pkg-config libssl-dev && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .

RUN cargo build --release

FROM debian:bookworm-slim

RUN apt-get update && \
    apt-get install -y libssl-dev ca-certificates && \
    update-ca-certificates && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .
COPY --from=builder /app/target/release/r-place-2 .

ENV WIDTH=128
ENV HEIGHT=128
ENV COOLDOWN_SEC=10
ENV UPDATE_COOLDOWN_SEC=60
ENV SMTP_SERVER=smtp.office365.com
ENV SMTP_PORT=587
ENV SMTP_USER=place.vannes.verif@outlook.com
ENV SMTP_PASSWORD=3N7slJuDaH9xDIh9(mdp modifier essayez pas lol)
ENV BIND_ADDRESS=0.0.0.0
ENV PORT=8080
ENV URL=http://localhost:8080
ENV JWT_SECRET=QerPT5YRenCmd54SQ6etMvRJ6LJagGt9KFLTCjwXs8Qg2hnuVnJhLpvVv47X8eFs
ENV FLAG=IUT{H3LL0_R4ND0M_US3R_N_9574}
ENV BASE_IMAGE=base.png
ENV SERVE_STATIC=true

EXPOSE 8080

CMD ["./r-place-2"]
