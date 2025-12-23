FROM alpine as builder
RUN apk add --no-cache cmake ninja g++ git libpq-dev
WORKDIR /opt/src
COPY CMakeLists.txt .
COPY src src
RUN cmake -B build -G Ninja && cmake --build build

FROM alpine
RUN apk add --no-cache libpq libstdc++
COPY --from=builder /opt/src/build/main /usr/local/bin/
CMD [ "/usr/local/bin/main" ]