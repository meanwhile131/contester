cmake_minimum_required(VERSION 3.10)
project(ContesterTestRunner)

include(FetchContent)
set(FETCHCONTENT_QUIET OFF)
FetchContent_Declare(
    libpqxx
    GIT_REPOSITORY https://github.com/jtv/libpqxx.git
    GIT_TAG 156d1f88d44ccd56c730f44e1d9f05b72f34e62b # v7.10.4
    GIT_PROGRESS ON
)
FetchContent_MakeAvailable(libpqxx)
add_executable(main src/main.cpp)
target_link_libraries(main PRIVATE libpqxx::pqxx)