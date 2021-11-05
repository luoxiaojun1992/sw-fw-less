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

    int ftruncateRes;
    ftruncateRes = ftruncate(fd, map_size);
    if (ftruncateRes != 0) {
        return ftruncateRes;
    }

    void *p_map;
    p_map = mmap(NULL, map_size, PROT_READ|PROT_WRITE, MAP_SHARED, fd, 0);
    if (p_map == MAP_FAILED) {
        return 1;
    }
    memcpy(p_map, content, map_size);

    int msyncRes;
    msyncRes = msync(p_map, map_size, MS_ASYNC);

    int munmapRes;
    munmapRes = munmap(p_map, map_size);

    if (msyncRes != 0) {
        return msyncRes;
    }

    return munmapRes;
}

int WriteFile(const char *pathname, const char *content) {
    int fd;
    fd = OpenFile(pathname);
    if (fd < 0) {
        return 1;
    }

    int writeFileByFdRes;
    writeFileByFdRes = WriteFileByFd(fd, content);

    int closeFileRes;
    closeFileRes = CloseFile(fd);

    if (writeFileByFdRes != 0) {
        return writeFileByFdRes;
    }

    return closeFileRes;
}

int AppendFileByFd(int fd, const char *content) {
    struct stat sb;

    int fstatRes;
    fstatRes = fstat(fd, &sb);
    if (fstatRes != 0) {
        return fstatRes;
    }

    int map_size;
    map_size = strlen(content) + sb.st_size;

    int lseekRes;
    lseekRes = lseek(fd, strlen(content) - 1, SEEK_END);
    if (lseekRes < 0) {
        return lseekRes;
    }

    int writeRes;
    writeRes = write(fd, "1", 1);
    if (writeRes < 1) {
        return 1;
    }

    void *p_map;
    p_map = mmap(NULL, map_size, PROT_READ|PROT_WRITE, MAP_SHARED, fd, 0);
    if (p_map == MAP_FAILED) {
        return 1;
    }
    memcpy(p_map + sb.st_size, content, strlen(content));

    int msyncRes;
    msyncRes = msync(p_map, map_size, MS_ASYNC);

    int munmapRes;
    munmapRes = munmap(p_map, map_size);

    if (msyncRes != 0) {
        return msyncRes;
    }

    return munmapRes;
}

int AppendFile(const char *pathname, const char *content) {
    int fd;
    fd = OpenFile(pathname);
    if (fd < 0) {
        return 1;
    }

    int appendFileByFdRes;
    appendFileByFdRes = AppendFileByFd(fd, content);

    int closeFileRes;
    closeFileRes = CloseFile(fd);

    if (appendFileByFdRes != 0) {
        return appendFileByFdRes;
    }

    return closeFileRes;
}

char * ReadFile(const char *pathname) {
    int fd;
    fd = open(pathname, O_RDWR);
    if (fd < 0) {
        return NULL;
    }

    struct stat sb;
    int fstatRes;
    fstatRes = fstat(fd, &sb);
    if (fstatRes != 0) {
        close(fd);
        return NULL;
    }

    void *p_map;
    p_map = mmap(NULL, sb.st_size, PROT_READ|PROT_WRITE, MAP_SHARED, fd, 0);
    if (p_map == MAP_FAILED) {
        close(fd);
        return NULL;
    }

    int closeRes;
    closeRes = close(fd);
    if (closeRes != 0) {
        return NULL;
    }

    return (char *)p_map;
}
