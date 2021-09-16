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
    if (fd < 0) {
        return 1;
    }

    ftruncate(fd, map_size);

    void *p_map;
    p_map = mmap(NULL, map_size, PROT_READ|PROT_WRITE, MAP_SHARED, fd, 0);
    if (p_map == MAP_FAILED) {
        close(fd);
        return 1;
    }
    memcpy(p_map, content, map_size);
    msync(p_map, map_size, MS_ASYNC);
    munmap(p_map, map_size);

    close(fd);

    return 0;
}

int AppendFile(const char *pathname, const char *content) {
    int fd;
    fd = open(pathname, O_RDWR);
    if (fd < 0) {
        return 1;
    }

    struct stat sb;
    fstat(fd, &sb);

    int map_size;
    map_size = strlen(content) + sb.st_size;
    lseek(fd, strlen(content) - 1, SEEK_END);
    write(fd, "1", 1);

    void *p_map;
    p_map = mmap(NULL, map_size, PROT_READ|PROT_WRITE, MAP_SHARED, fd, 0);
    if (p_map == MAP_FAILED) {
        close(fd);
        return 1;
    }
    memcpy(p_map + sb.st_size, content, strlen(content));
    msync(p_map, map_size, MS_ASYNC);
    munmap(p_map, map_size);

    close(fd);

    return 0;
}

char * ReadFile(const char *pathname) {
    int fd;
    fd = open(pathname, O_RDWR);
    if (fd < 0) {
        return NULL;
    }

    struct stat sb;
    fstat(fd, &sb);

    void *p_map;
    p_map = mmap(NULL, sb.st_size, PROT_READ|PROT_WRITE, MAP_SHARED, fd, 0);
    if (p_map == MAP_FAILED) {
        close(fd);
        return NULL;
    }

    close(fd);

    return (char *)p_map;
}
