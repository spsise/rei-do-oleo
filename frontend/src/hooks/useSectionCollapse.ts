import { useState, useCallback } from 'react';

export type SectionType = 'client' | 'vehicles' | 'services' | 'summary';

interface UseSectionCollapseReturn {
  collapsedSections: Set<SectionType>;
  toggleSection: (section: SectionType) => void;
  isSectionCollapsed: (section: SectionType) => boolean;
  expandAllSections: () => void;
  collapseAllSections: () => void;
}

export const useSectionCollapse = (): UseSectionCollapseReturn => {
  const [collapsedSections, setCollapsedSections] = useState<Set<SectionType>>(
    new Set()
  );

  const toggleSection = useCallback((section: SectionType) => {
    setCollapsedSections((prev) => {
      const newSet = new Set(prev);
      if (newSet.has(section)) {
        newSet.delete(section);
      } else {
        newSet.add(section);
      }
      return newSet;
    });
  }, []);

  const isSectionCollapsed = useCallback(
    (section: SectionType) => {
      return collapsedSections.has(section);
    },
    [collapsedSections]
  );

  const expandAllSections = useCallback(() => {
    setCollapsedSections(new Set());
  }, []);

  const collapseAllSections = useCallback(() => {
    setCollapsedSections(new Set(['client', 'vehicles', 'services', 'summary']));
  }, []);

  return {
    collapsedSections,
    toggleSection,
    isSectionCollapsed,
    expandAllSections,
    collapseAllSections,
  };
}; 