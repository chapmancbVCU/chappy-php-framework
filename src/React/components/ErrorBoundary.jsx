import React from 'react';

/**
 * A minimal React Error Boundary that catches render-time errors thrown by its
 * descendant components and renders a generic fallback message instead of
 * breaking the entire React tree. Errors are also logged to the console.
 *
 * Typical usage:
 * ```jsx
 * <ErrorBoundary>
 *   <ProfilePage />
 * </ErrorBoundary>
 * ```
 *
 * @augments React.Component<ErrorBoundaryProps, ErrorBoundaryState>
 */
export default class ErrorBoundary extends React.Component {
  state = { error: null };
  static getDerivedStateFromError(error) { return { error }; }
  componentDidCatch(error, info) { console.error('[ErrorBoundary]', error, info); }

  render() {
    if (this.state.error) {
      return <div className="alert alert-danger">Something went wrong.</div>;
    }
    return this.props.children;
  }
}
