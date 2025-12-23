cmake_minimum_required(VERSION 3.11)
project(ContesterTestRunner)

set(CMAKE_CXX_STANDARD 17)
set(CMAKE_CXX_STANDARD_REQUIRED ON)
set(CMAKE_CXX_EXTENSIONS OFF)
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