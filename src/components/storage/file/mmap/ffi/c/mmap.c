#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <fcntl.h>
#include <sys/mman.h>
#include <string.h>

int WriteFile(const char *pathname, const char *content) {
    int map_size;
    map_size = strlen(content);

    int fd;
    fd = open(pathname, O_RDWR);
    ftruncate(fd, map_size);

//    struct stat sb;
//    fstat(fd, &sb);

    void *p_map;
    p_map = mmap(NULL, map_size, PROT_READ|PROT_WRITE, MAP_SHARED, fd, 0);
    if (p_map == MAP_FAILED) {
        return 1;
    }
    memcpy(p_map, content, map_size);
    msync(p_map, map_size, MS_ASYNC);
    munmap(p_map, map_size);

    close(fd);

    return 0;
}

int main() {
    return WriteFile("/Users/luoxiaojun/php/sw-fw-less/src/components/storage/file/mmap/ffi/c/test.txt", "123456789");
}
