cmake_minimum_required(VERSION 3.10)
project(ContesterTestRunner)

set(CMAKE_CXX_STANDARD 17)
set(CMAKE_CXX_STANDARD_REQUIRED ON)
set(CMAKE_CXX_EXTENSIONS OFF)
add_subdirectory(libpqxx)
add_executable(main src/main.cpp)
target_link_libraries(main PRIVATE libpqxx::pqxx)