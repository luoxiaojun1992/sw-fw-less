#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <fcntl.h>
#include <sys/mman.h>
#include <string.h>

int OpenFile(const char *pathname) {
    return open(pathname, O_RDWR|O_CREAT, S_IRUSR|S_IWUSR);
}

int CloseFile(int fd) {
    return close(fd);
}

int WriteFileByFd(int fd, const char *content) {
    int map_size;
    map_size = strlen(content);

    ftruncate(fd, map_size);

    void *p_map;
    p_map = mmap(NULL, map_size, PROT_READ|PROT_WRITE, MAP_SHARED, fd, 0);
    if (p_map == MAP_FAILED) {
        return 1;
    }
    memcpy(p_map, content, map_size);
    msync(p_map, map_size, MS_ASYNC);
    munmap(p_map, map_size);

    return 0;
}

int WriteFile(const char *pathname, const char *content) {
    int fd;
    fd = OpenFile(pathname);
    if (fd < 0) {
        return 1;
    }

    WriteFileByFd(fd, content);

    CloseFile(fd);

    return 0;
}

int AppendFileByFd(int fd, const char *content) {
    struct stat sb;
    fstat(fd, &sb);

    int map_size;
    map_size = strlen(content) + sb.st_size;
    lseek(fd, strlen(content) - 1, SEEK_END);
    write(fd, "1", 1);

    void *p_map;
    p_map = mmap(NULL, map_size, PROT_READ|PROT_WRITE, MAP_SHARED, fd, 0);
    if (p_map == MAP_FAILED) {
        return 1;
    }
    memcpy(p_map + sb.st_size, content, strlen(content));
    msync(p_map, map_size, MS_ASYNC);
    munmap(p_map, map_size);

    return 0;
}

int AppendFile(const char *pathname, const char *content) {
    int fd;
    fd = OpenFile(pathname);
    if (fd < 0) {
        return 1;
    }

    AppendFileByFd(fd, content);

    CloseFile(fd);

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
