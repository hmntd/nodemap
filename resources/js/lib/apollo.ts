import { ApolloClient, InMemoryCache, HttpLink } from '@apollo/client/core';

// Get CSRF Token from meta tag or cookie
const csrfToken =
    (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)
        ?.content || '';

const httpLink = new HttpLink({
    uri: '/graphql',
    headers: {
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json',
    },
});

export const apolloClient = new ApolloClient({
    link: httpLink,
    cache: new InMemoryCache(),
    defaultOptions: {
        watchQuery: {
            fetchPolicy: 'cache-and-network',
        },
    },
});
