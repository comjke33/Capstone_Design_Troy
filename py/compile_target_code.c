#include <stdio.h>
#include <string.h>
int main()
{
    int a;
    scanf("%d",&a);
    char str[100];
    int arr[100]={0};
    scanf("%s",str);
    int sum=0;
    for(int i=0;i<strlen(str);i++)
    {
        arr[i]=str[i];
        arr[i]+=1-'1';
        sum+=arr[i];
    }
    printf("%d",sum);
}
