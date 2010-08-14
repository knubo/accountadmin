//
//  PersonView.h
//
//  Created by Knut Erik Borgen on 13.08.10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>
#import <Foundation/Foundation.h>
#import "FrittRegnskapAppDelegate.h"

@interface PersonView : UIView {
	IBOutlet FrittRegnskapAppDelegate *appDelegate;
	NSArray *people;
	NSMutableArray *indexes;
	NSMutableArray *indexNames;
}

@property (nonatomic, retain) NSArray *people;
@property (nonatomic, retain) NSMutableArray *indexes;
@property (nonatomic, retain) NSMutableArray *indexNames;


- (void)loadPeople;
- (void) calculateIndexes;


@end
